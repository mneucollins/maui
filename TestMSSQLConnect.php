<?php
error_reporting(E_ALL);

$registrar = mssql_connect ("registrardatabase.iowa.uiowa.edu", "app_passport", "pW47Z@clN");
if (!$registrar) {
    die('Something went wrong while connecting to the registrar database');
}

$passport =  mysql_connect ('localhost', 'passport_user', 'CftQubXAXZUy9Ku7');
if (!$passport) {
    die('Something went wrong while connecting to the passport database');
}
$sql = "DELETE * FROM `_maui_students` ";


mssql_select_db('whouse', $registrar);

$sql = "SELECT [SESSION], [univid], [hawkid], [LAST_NAME], [FIRST_NAME], [COLLEGE], [CLASS], [DEPT], [COURSE], [SECTION], [HOURS]
			FROM [whouse].[dbo].[vw_passport]";

$students = mssql_query ($sql);

while ($row=mssql_fetch_array($students)) {

}
print_r ($row);

?>