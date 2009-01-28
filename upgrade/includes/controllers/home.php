<?php
class Home extends Controller {

	function Home()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$this->load->view('header');
		$this->load->view('home');
		$this->load->view('footer');
	}
	
	function step_1()
	{
		if($this->_copyConfigFiles())
		{
			$data = array();
			$this->load->view('header');
			$this->load->view('upgrade/step1', $data);
			$this->load->view('footer');
		}
		else
		{
			$this->session->set_flashdata('msg', 'Upgrade config files cannot be written to. Please check permissions');
			header("Location: index.php?c=home&m=index");
		}
	}
	
	function step_2()
	{
		$this->load->database();
		
		// loop through and run all updates
		while($this->_runUpgradeProcess())
		{
			continue;
		}
		
		$this->load->view('header');
		$this->load->view('upgrade/step2');
		$this->load->view('footer');
	}
	
	function _copyConfigFiles()
	{
		include('path_config.php');
		
		if(!file_exists($application_folder_location.'config/database.php'))
		{
			return false;
		}
		
		if(!is_writable(APPPATH.'config/database.php'))
		{
			return false;
		}
		
		file_put_contents(APPPATH.'config/database.php', file_get_contents($application_folder_location.'config/database.php'));
		return true;
	}
	
	function _runUpgradeProcess()
	{
		if($this->_getDbVersion() != $this->_getFileVersion())
		{
			$this->_executeUpgrade();
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function _executeUpgrade()
	{
		$ver = $this->_getDbVersion();
		if(!$ver)
		{
			die('The Database has no version number, please consult the xtraupload support forums for more information.');
		}
		include(APPPATH.'sql/sql_'.$ver.'.php');
	}
	
	function _getDbVersion()
	{
		return @$this->db->get_where('config', array('name' => '_db_version'))->row()->value;
	}
	
	function _getFileVersion()
	{
		return '2.0.0,0.0.2.0';
	}
}