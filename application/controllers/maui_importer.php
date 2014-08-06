<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Maui_Importer extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        //the following does not work from local machine.
        $this->registrardb = $this->load->database('registrar', TRUE);
        $this->passportdb = $this->load->database('passport', TRUE);
        $this->maui = $this->load->database('maui_import', TRUE);
    }

    
    public function index() {
        $data['students'] = $this->fetch_maui_students();
        $data['update_email'] = $this->update_email();

//        $data['role_updates'] = $this->update_roles();
//        $data['name_updates'] = $this->update_names();
//        $data['dropped_students'] = $this->handle_drops();
        if (!$this->input->is_cli_request()){
            echo "uncomment load->view in controller to see list";
//           $this->load->view('student_list', $data);
        }
    }
        
    function fetch_maui_students() {

        //fred ($where, "here1");
        $sql = "TRUNCATE TABLE `_maui_students`";
        $this->maui->query($sql);

        $sql = "SELECT [SESSION], [univid], [hawkid], [LAST_NAME], [FIRST_NAME], [COLLEGE], [CLASS], [DEPT], [COURSE], [SECTION], [HOURS]
			FROM [whouse].[dbo].[vw_passport]";
//        $sql = "SELECT [SESSION], [univid], [hawkid], [email], [LAST_NAME], [FIRST_NAME], [COLLEGE], [CLASS], [DEPT], [COURSE], [SECTION], [HOURS]
//			FROM [whouse].[dbo].[vw_passport]";
        $students = $this->registrardb->query($sql);
        
        //fred ($students->num_rows(), "num rows-students");
 
        foreach ($students->result() as $student) {
            $insertArray = array(
                'SESSION' => $student->SESSION, 'univid' => $student->univid, 'hawkid' => $student->hawkid, 'LAST_NAME' => $student->LAST_NAME, 'FIRST_NAME' => $student->FIRST_NAME, 'COLLEGE' => $student->COLLEGE,
//                'SESSION' => $student->SESSION, 'univid' => $student->univid, 'hawkid' => $student->hawkid, $student->email, 'LAST_NAME' => $student->LAST_NAME, 'FIRST_NAME' => $student->FIRST_NAME, 'COLLEGE' => $student->COLLEGE,
                'CLASS' => $student->CLASS, 'DEPT' => $student->DEPT, 'COURSE' => $student->COURSE, 'SECTION' => $student->SECTION, 'HOURS' => $student->HOURS);
            $this->maui->insert('_maui_students', $insertArray);
        }

        $this->load->library('table');
        $maui_students = $this->maui->get('_maui_students');
        $data = $this->table->generate($maui_students);

        if (empty($data)) {$data = "no student data received";}
        return $data;
    }
    
    function update_email() {
        $sql = "UPDATE _maui_students JOIN _classlist ON _maui_students.hawkid = _classlist.hawkid SET _maui_students.email = _classlist.email";
        $this->maui->query($sql);
    }

    function run_updates() {
        //invoked from the command line with 
        //php /local/www/vhosts/dsph-dev.provost.uiowa.edu/htdocs/maui/index.php maui_importer run_updates
        $sql = "INSERT INTO users_roles (uid) SELECT users.uid FROM users
                    JOIN cas_user ON cas_user.uid = users.uid
                    LEFT JOIN users_roles ON users_roles.uid = users.uid
                    WHERE users_roles.uid is null";
        $query = $this->passportdb->query($sql);
        $sql = "UPDATE users_roles
                    JOIN users ON users_roles.uid = users.uid
                    JOIN cas_user ON cas_user.uid = users.uid
                    JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
                    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
                    SET users_roles.rid = 5
                    WHERE _maui_ignore.uid is null";
        $query = $this->passportdb->query($sql);        
        $sql = "UPDATE  `field_data_field_last_name`
                    JOIN `cas_user` ON `field_data_field_last_name`.`entity_id` = `cas_user`.`uid`
                    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
                    SET `field_last_name_value` = `_maui_students`.`LAST_NAME`";
        $query = $this->passportdb->query($sql);
        $sql = "UPDATE  `field_data_field_first_name`
                    JOIN `cas_user` ON `field_data_field_first_name`.`entity_id` = `cas_user`.`uid`
                    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
                    SET `field_first_name_value` = `_maui_students`.`FIRST_NAME`";
        $query = $this->passportdb->query($sql);
        $sql = "INSERT INTO _maui_students_dropped (uid) 
                    SELECT users_roles.uid FROM users_roles
                    JOIN users ON users_roles.uid = users.uid
                    JOIN cas_user ON cas_user.uid = users.uid
                    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
                    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
                    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null AND users_roles.uid = 5";
        $query = $this->passportdb->query($sql);
        $sql = "UPDATE users_roles
                    JOIN users ON users_roles.uid = users.uid
                    JOIN cas_user ON cas_user.uid = users.uid
                    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
                    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
                    SET users_roles.rid = 1
                    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null";
        $query = $this->passportdb->query($sql);
        echo "done";
    }
    
    function registration_email() {
        $this->load->library('email');
        $config['wordwrap']=TRUE;
        $config['wrapchars']=80;
        $congig['mailtype'] ="html";
        
        $sql = "INSERT INTO _maui_email_log (uid) 
                    SELECT users_roles.uid FROM users_roles 
                    LEFT JOIN _maui_email_log ON users_roles.uid = _maui_email_log.uid
                    LEFT JOIN _maui_ignore ON users_roles.uid = _maui_ignore.uid
                    WHERE _maui_ignore.uid is null AND _maui_email_log.uid is null AND users_roles.rid = 5";
        $query = $this->passportdb->query($sql);
        
        $sql = "SELECT users.uid, users.mail 
                    FROM _maui_email_log 
                    JOIN users ON _maui_email_log.uid = users.uid 
                    WHERE _maui_email_log.welcome_sent is null";
        $query = $this->passportdb->query($sql);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row){
                $emailto = $row->mail;
                $message = "Congratulations! You've successfully registered for the Passport Project website.\n\n";
                $message .= "Please close your browser window, come back in 5 minutes. This will give the system time to notice you've joined the website.\n\n";
                $message .= "When you come back, open a new window, and log in. We then invite you to explore the website, including to check out the events calendar.\n\n";
                $message .= "Next week we'll go into greater detail about how to make the most of the website.\n\n";
                $message .= "Thanks. We look forward to the semester and cultural travels ahead!";

                $this->email->clear();
                $this->email->from('jon-winet@uiowa.edu', 'The Passport Team ');
                $this->email->to($emailto);
                $this->email->subject('Congratulations!');
                $this->email->message($message);
                $this->email->send();
                
                echo $this->email->print_debugger();
                
                $sql = "UPDATE _maui_email_log SET welcome_sent = 1 WHERE _maui_email_log.uid = ". $row->uid;
                $this->passportdb->query($sql);
            }
        }
    }
    
    function update_roles() {
        $data = '';
        $sql = "SELECT _maui_students.hawkid, cas_user.uid, users_roles.rid  FROM _maui_students 
                            LEFT JOIN cas_user ON _maui_students.hawkid = cas_user.cas_name
                            LEFT JOIN users_roles ON cas_user.uid = users_roles.uid
                            LEFT JOIN _maui_ignore ON users_roles.uid = _maui_ignore.uid
                            WHERE cas_user.uid IS NOT NULL
                            AND _maui_ignore.uid IS NULL";
        
        $role_updates = $this->passportdb->query($sql);
        foreach ($role_updates->result() as $role_update) {
            $updateArray = array('rid' => 5, 'uid' => $role_update->uid );
            $this->passportdb->update('users_roles', $updateArray);
            $this->passportdb->update('_maui_students_added', array('uid'=> $role_update->uid));
            $data .= $role_update->hawkid."<br />";
        }
        if (empty($data)) { $data = 'no role updates';}
        return $data;
    }
    
    function update_names() {
        $data = "";
        $sql = "UPDATE  `field_data_field_last_name`
                    JOIN `cas_user` ON `field_data_field_last_name`.`entity_id` = `cas_user`.`uid`
                    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
                SET `field_last_name_value` = `_maui_students`.`LAST_NAME` ";
        $this->passportdb->query($sql);

        $lastnames = $this->db->affected_rows();
        $data .= empty($lastnames) ? "No last name updates <br />" : "Number of updated last names: ".$this->db->affected_rows()."<br />";
        
 
        $sql = "UPDATE  `field_data_field_first_name`
                    JOIN `cas_user` ON `field_data_field_first_name`.`entity_id` = `cas_user`.`uid`
                    JOIN `_maui_students` ON `cas_user`.`cas_name` =`_maui_students`.`hawkid`
                SET `field_first_name_value` = `_maui_students`.`FIRST_NAME`";
        $this->passportdb->query($sql);
        $firstnames = $this->db->affected_rows();
        $data .= empty($firstnames) ? "No first name updates <br />" : "Number of updated First Names: ".$this->db->affected_rows()."<br />";
        
        return $data;
    }
    
    function handle_drops() {      
        //don't actually delete person just update the role
        
        $sql = "INSERT INTO _maui_students_dropped (uid) 
                    SELECT users_roles.uid FROM users_roles
                    JOIN users ON users_roles.uid = users.uid
                    JOIN cas_user ON cas_user.uid = users.uid
                    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
                    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
                    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null AND users_roles.uid = 5";
        
        $sql = "UPDATE users_roles
                    JOIN users ON users_roles.uid = users.uid
                    JOIN cas_user ON cas_user.uid = users.uid
                    LEFT JOIN _maui_students ON cas_user.cas_name = _maui_students.hawkid
                    LEFT JOIN _maui_ignore ON _maui_ignore.uid = users.uid
                    SET users_roles.rid = 1
                    WHERE _maui_ignore.uid is null AND _maui_students.hawkid is null" ;

        $drops = $this->db->affected_rows();
        $data = empty($drops) ? "No students dropped <br />" : "Number of dropped students: ".$this->db->affected_rows()."<br />";
        return $data;
    }
    
	function loadClassList() {

		$this->load->model('excel_survey');

		$data['msg'] = "";
		//$action = $this->input->post('action');
		if ($this->input->post('return')) {
			redirect('mainmenu');
		} elseif ($this->input->post('upload')) {
			$config['upload_path'] = APPPATH . 'import_data/';
			$config['allowed_types'] = 'xls';
			//$config['max_size'] = '32000';
			$config['max_size'] = '0';
			$config['file_name'] = 'SurveyDefs.xls';
			$config['overwrite'] = TRUE;
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if (!$this->upload->do_upload()) {
				$data['errmsg'] = $this->upload->display_errors();
			} else {
				//proc the excel file
				$uploadedfilename = $GLOBALS['_FILES']['userfile']['name'];
				
				$filename = $config['upload_path'] . $config['file_name'];

				$msg = $this->excel_survey->loadSurveyDefs($filename);

				$this->load->model('user');
				$user_name = $this->user->userName($this->session->userdata('user_id'));
				
				if (!empty($msg)){
					$data['errmsg']= "$uploadedfilename: $msg";
					$data['msg']= '';
				} else {
					$data['msg'] .= "$uploadedfilename: excel file processed, survey definitions loaded";
					$data ['errmsg'] = '';
				}
				$msg = (!empty($data['errmsg'])) ? $data['errmsg'] : $data['msg'];
				$sql = "INSERT INTO `surveydefs_import_log` (`user`, `msg`) VALUES (".$this->db->escape($user_name).", 
".$this->db->escape($msg).")";
				$query=$this->db->query($sql);
			}

		} elseif ( $this->input->post('review')) {
			redirect('mainmenu/show_surveydefs_import_log');

		} elseif ($this->input->post('logout')) {
			redirect('mainmenu/logout');

		} 
		$this->load->view('loadSurveyDefs_view', $data);
	}

    
    
}

?>

