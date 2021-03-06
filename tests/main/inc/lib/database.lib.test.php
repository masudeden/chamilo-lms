<?php
require_once(api_get_path(LIBRARY_PATH).'database.lib.php');
require_once(api_get_path(LIBRARY_PATH).'add_course.lib.inc.php');
require_once(api_get_path(LIBRARY_PATH).'course.lib.php');
class TestDatabase extends UnitTestCase {

	 public $dbase;
	 public function TestDatabase() {
	 	$this->UnitTestCase('Database library - main/inc/lib/database.lib.test.php');
	 }

	 public function setUp() {
	 	global $_configuration;
 	 	$this->dbase = new Database();
	 }

	 public function tearDown() {
	 	$this->dbase = null;
	 }

	public function testAffectedRows() {
		$res=$this->dbase->affected_rows();
		$this->assertTrue(is_numeric($res));
	}

	public function testCountRows() {
		$table='class';
		$res=$this->dbase->count_rows($table);
		if(!is_string($res)){
			$this->assertTrue(is_numeric($res));
		}
	}

	public function testError() {
		$res=$this->dbase->error();
		$this->assertTrue(is_string($res));
	}

	public function testEscapeString() {
		$string='Lore"May';
		$res=$this->dbase->escape_string($string);
		$this->assertTrue(is_string($res));
	}

	public function testFetchArray() {
		$sql = 'select 1';
		$res=Database::query($sql);
		$resu=$this->dbase->fetch_array($res);
		$this->assertTrue(is_array($resu));
		$this->assertFalse(is_null($resu));
	}

	public function testFetchArrayError() {
		$sql = 'SELECT  1';
		$res=Database::query($sql);
		$resu=$this->dbase->fetch_array($res);
		$this->assertTrue(is_array($resu));
	}

	function testFetchObject() {
		$sql = 'SELECT  1';
		$res=Database::query($sql);
		$resu=$this->dbase->fetch_object($res);
		$this->assertTrue(is_object($resu));
	}

	function testFetchRow() {
		$sql = 'SELECT  1';
		$res=Database::query($sql);
		$resu=$this->dbase->fetch_row($res);
		$this->assertTrue(is_array($resu));
	}

	/* // Contains a private unaccessible method, Database::fix_database_parameter().
	function testFixDatabaseParameterReturnString() {
		$course_info = api_get_course_info();
		$database_name= $course_info["dbName"];
		$res=$this->dbase->fix_database_parameter($course_info);
		$this->assertTrue(is_string($res));
	}
	*/

	/* // Contains a private unaccessible method, Database::format_glued_course_table_name().
	function testFormatGluedCourseTableName()  {
		$database_name_with_glue='';
		$table='';
		$res=$this->dbase->format_glued_course_table_name($database_name_with_glue, $table);
		$this->assertTrue(is_string($res));
	}
	*/

	/* // Contains a private unaccessible method, Database::format_table_name().
	function testFormatTableName() {
		$database='';
		$table='';
		$res=$this->dbase->format_table_name($database, $table);
		$this->assertTrue(is_string($res));
	}
	*/

	function testGetCourseInfo() {
		$course_code='AYDD';
		$res=$this->dbase->get_course_info($course_code);
		$this->assertTrue(is_array($res));
	}

	function testGetCourseList() {
		$sql_query = "SELECT * FROM $table";
		$res=$this->dbase->get_course_list($sql_query);
		$this->assertTrue(is_array($res));
	}

	function testGetCourseTable() {
		$short_table_name='';
		$database_name='';
		$res=$this->dbase->get_course_table($short_table_name,$database_name);
		$this->assertTrue(is_string($res));
	}

	function testGetMainDatabase() {
		global $_configuration;
		$res=$this->dbase->get_main_database();
		$this->assertTrue(is_string($res));
	}

	function testGetMainTable() {
		$short_table_name='';
		$res=$this->dbase->get_main_table($short_table_name);
		$this->assertTrue(is_string($res));
	}

	/* // Contains a private unaccessible method, Database::glue_course_database_name().
	function testGlueCourseDatabaseName() {
		$database_name='';
		$res=$this->dbase->glue_course_database_name($database_name);
		$this->assertTrue(is_string($res));
		$this->assertTrue($res);
	}
	*/

	function testInsertId() {
		$res = $this->dbase->insert_id();
		$this->assertTrue(is_numeric($res));
	}

	function testNumRows() {
		$sql = 'SELECT * FROM user';
		$res = Database::query($sql);
		$resul=Database::num_rows($res);
		$this->assertTrue(is_numeric($resul));
	}

	function testQuery() {
		$sql = 'SELECT 1';
		$res = Database::query($sql);
		$this->assertTrue(is_resource($res));
	}

	function testResult() {
		$sql = 'SELECT email FROM user';
		$resource = Database::query($sql);
		$res = Database::result($resource, 1);
		$this->assertTrue(is_string($res));
	}

	function testStoreResult(){
		$sql = 'SELECT 1';
		$resource = $this->dbase->query($sql);
		$res = $this->dbase->store_result($resource);
		$this->assertTrue(is_array($res));
	}
}
?>
