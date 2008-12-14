<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * XtraUpload
 *
 * A turn-key open source web 2.0 PHP file uploading package requiring PHP v5
 *
 * @package		XtraUpload
 * @author		Matthew Glinski
 * @copyright	Copyright (c) 2006, XtraFile.com
 * @license		http://xtrafile.com/docs/license
 * @link		http://xtrafile.com
 * @since		Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * XtraUpload Files DB Model
 *
 * @package		XtraUpload
 * @subpackage	Model
 * @category	Model
 * @author		Matthew Glinski
 * @link		http://xtrafile.com/docs/pages/files
 */

// ------------------------------------------------------------------------

class Files_db extends Model 
{
    public function Files_db($select='')
    {
        // Call the Model constructor
        parent::Model();
    }
	
	//------------------------
	// File Viewing Functions
	//------------------------
	public function getFiles($limit=100, $offset=0, $select='')
	{
		$posts = array();
		$this->db->order_by("refrence.id", "desc"); 
		if($select != '')
		{
			$this->db->select($select);
		}

		$query = $this->db->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('user' => $this->session->userdata('id')), $limit, $offset);
		return $query;	
	}
	
	public function getAdminFiles($sort, $direction, $limit=100, $offset=0, $select='')
	{
		$this->db->order_by($sort, $direction); 
		if($select != '')
		{
			$this->db->select($select);
		}

		return $this->db->join('files', 'refrence.link_id = files.id')->get('refrence', $limit, $offset);
	}
	
	public function getAdminFiles_search($query, $sort, $direction, $limit=100, $offset=0, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}

		$this->db->like('o_filename', $query);
		$this->db->or_like('descr', $query);
		$this->db->or_like('file_id', $query);
		$this->db->order_by($sort, $direction); 
		return $this->db->join('files', 'refrence.link_id = files.id')->get('refrence', $limit, $offset);
	}
	
	public function getImages($limit=100, $offset=0, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}

		$this->db->order_by("refrence.id", "desc"); 		
		return $this->db->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('user' => $this->session->userdata('id'), 'refrence.is_image' => 1), $limit, $offset);
	}
	
	public function getNumFiles()
	{
		$query = $this->db->select('id')->where('user', $this->session->userdata('id'));
		return $query->count_all_results('refrence');
	}
	
	public function getAdminNumFiles()
	{
		return $this->db->select('id')->count_all_results('refrence');
	}
	
	public function getAdminNumFiles_search($query)
	{
		$this->db->like('o_filename', $query);
		$this->db->or_like('descr', $query);
		$this->db->or_like('file_id', $query);
		
		return $this->db->select('id')->count_all_results('refrence');
	}
	
	public function getFilesUseageSpace($user = '')
	{
		if(empty($user))
		{
			$user = $this->session->userdata('id');
		}
		
		$query = $this->db->select_sum('size')->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('user' => $user));
		return $query->row()->size;
	}
	
	public function getFileById($id, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}

		$query = $this->db->get_where('refrence', array('id' => $id));
		return $query->row();
	}
	
	public function getFileForDownload($id, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}

		$query = $this->db->get_where('files', array('id' => $id));
		if($query->num_rows() != 1)
		{
			return false;
		}
		return $query->row();
	}
	
	public function getRecentFiles($limit=5, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}

		$this->db->order_by("id", "desc"); 
		$query = $this->db->get('refrence', $limit, 0);
		
		return $query;
	}
	
	public function getLinks($secid, $fileObject=false)
	{
		$links = array();
		
		// Use provided file object
		if($fileObject)
		{
			$links['down'] = site_url('/files/get/'.$fileObject->file_id.'/'.$fileObject->link_name);
			$links['del'] = site_url('/files/delete/'.$fileObject->file_id.'/'.$fileObject->secid.'/'.$fileObject->link_name);
			
			if($fileObject->is_image)
			{
				$links['img'] = site_url('/image/links/'.$fileObject->file_id.'/'.$fileObject->link_name);
			}
			return $links;
		}
		
		// No provided file object, make one
		$query = $this->db->select('file_id, link_name, is_image')->get_where('refrence', array('secid' => $secid), 1, 0);
		if($query->num_rows() == 1)
		{
			$file = $query->row();
			
			$links['down'] = site_url('/files/get/'.$file->file_id.'/'.$file->link_name);
			$links['del'] = site_url('/files/delete/'.$file->file_id.'/'.$secid.'/'.$file->link_name);
			
			if($file->is_image)
			{
				$links['img'] = site_url('/image/links/'.$file->file_id.'/'.$file->link_name);
			}
			return $links;
			
		}
		else
		{
			return false;
		}
	}
	
	public function fileExists($id, $secid)
	{
		$query = $this->db->select('id')->get_where('refrence', array('file_id' => $id, 'secid' => $secid), 1, 0);
		if($query->num_rows() > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function getDownloadLink($id)
	{
		$query = $this->db->select('file_id, link_name')->get_where('refrence', array('file_id' => $id), 1, 0);
		if($query->num_rows() == 1)
		{
			$file = $query->row();
			return site_url('/files/get/'.$file->file_id.'/'.$file->link_name);
			
		}
		else
		{
			return false;
		}
	}
	
	public function getFileObject($id, $select='')
	{
		return $this->_getFileObject($id, $select);
	}
	
	public function getFileRefrence($id, $name, $select='')
	{
		if($select != '')
		{
			$this->db->select($select);
		}
		$query = $this->db->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('file_id' => $id, 'link_name' => $name), 1, 0);
		if($query->num_rows() == 1)
		{
			return $query->row();
		}
		else
		{
			return false;
		}
	}
	
	public function getImageLinks($fid, $query=NULL)
	{
		$query = $this->db->select('file_id, link_name, server, filename, thumb')->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('file_id' => $fid, 'files.is_image' => 1), 1, 0);
		if($query->num_rows() != 1)
		{
			return false;
		}
		
		$file = $query->row();

		$links = array(
			'code_url' => site_url('/image/links/'.$file->file_id.'/'.$file->link_name),
			'img_url' => site_url('/image/show/'.$file->file_id.'/'.$file->link_name),
			'img_path' => $file->filename,
			'thumb_url' => site_url('/image/thumb/'.$file->file_id.'/'.$file->link_name, $file->server),
			'thumb_path' => $file->thumb,
			'direct_url' => site_url('/image/direct/'.$file->file_id.'/'.$file->link_name, $file->server)
		);
		return $links;
	}
	
	public function process_image($file, $type, $new, $prefix)
	{
		if(!is_dir('thumbstore/'.$prefix))
		{
			mkdir('thumbstore/'.$prefix);
		}
		$config['image_library'] = 'GD2';
		$config['create_thumb'] = TRUE;
		$config['thumb_marker '] = '';
		$config['source_image'] = $file;
		$config['new_image'] = $new;
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['quality'] = 90;
		$config['width'] = 200;
		$config['height'] = 200;
		$this->load->library('image_lib', $config);
		
		$this->image_lib->resize();
	}
	
	public function newFile($file, $uid, $pass, $desc, $feature, $user, $is_image, $server, $remote_upload, $select='')
	{
		// Get MD5 Hash of uploaded file
		$md5 = md5_file($file);
		
		// Filesize of uploaded file
		$size = filesize($file);
		
		// Has file been baned?
		$banFile = $this->db->select('id')->get_where('bans', array('md5' => $md5));
		if($banFile->num_rows() != 0)
		{
			// YES!!!  KILL IT WITH FIRE!!!
			@unlink($file);
			log_message('debug', 'File Uploaded is banned');
			return false;
		}
		
		//is storage limit set?
		if($this->startup->group_config->storage_limit > 0)
		{
			// Does user have sufficent space to upload this file?
			if(($this->getFilesUseageSpace($user) + $size) > ($this->startup->group_config->storage_limit * 1024 * 1024))
			{
				// Nope!  KILL IT WITH FIRE!!!
				@unlink($file);
				
				log_message('debug', 'File Uploaded excedes allowed storage space for user');
				return false;
			}
		}
		
		// Generate an awesome file_id for our new friend
		$file_id = $this->functions->getRandId();
		
		if(!$user)
		{
			$user = '';
		}
			
		// Has the file been uploaded before?
		$realFile = $this->db->select('id, type, is_image')->get_where('files', array('md5' => $md5));
		if($realFile->num_rows() == 0)
		{// Nope, original content!
			
			// Get some file information
			$type =  str_replace('.','',strtolower(strrchr(basename($file), '.')));
			$rType =  str_replace('.','',strrchr(basename($file), '.'));
			$new_image = '';
			
			// Create file storage folder if it dosent exist
			$prefix = substr($md5, 0, 2);
			$new_path = 'filestore/'.$prefix.'/'.$file_id.'.'.basename($file);
			if(!is_dir('filestore/'.$prefix))
			{
				mkdir('filestore/'.$prefix);
			}
			
			// Was an image uploaded? If so, Process It! 
			if($is_image and $size <= (15 * 1024 * 1024))
			{
				$new_image = 'thumbstore/'.$prefix.'/'.$file_id.'.'.basename($file);
				$this->process_image($file, $type, $new_image, $prefix);
				$base = basename($file);
				$base = substr($base,0,(strlen($base) - (1+strlen($type))));
				$base = $base.'_thumb.'.$rType;
				$new_image = 'thumbstore/'.$prefix.'/'.$file_id.'.'.$base;
			}
			
			// Move the file into its new home
			if(is_uploaded_file($file))
			{
				move_uploaded_file($file, $new_path);
			}
			else
			{
				rename($file, $new_path);
			}
			
			// Create the `files` entry to store our file
			$data = array(
				'filename' => $new_path,
				'size' => $size,
				'md5' => $md5,
				'status' => 1,
				'is_image' => $is_image,
				'thumb' => $new_image,
				'type' => $type,
				'server' => $server,
				'prefix' => $prefix
			);
			$this->db->insert('files', $data); unset($data);
			$fileLinkId = $this->db->insert_id();
		}
		else
		{
			// Oops, we have a dupe. Lets save the user some trouble and not tell them, mmmk?
			$fileObj = $realFile->row();
			$type = $fileObj->type;
			$fileLinkId = $fileObj->id;
			$is_image = $fileObj->is_image;
			@unlink($file);
		}
		
		// Create an entry in the refrence table to this new upload
		$data = array(
			'o_filename' => basename($file), 
			'file_id' => $file_id,
			'link_id' => $fileLinkId,
			'status' => 1,
			'type' => $type,
			'is_image' => $is_image,
			'ip' => $_SERVER['REMOTE_ADDR'],
			'descr' => $desc,
			'pass' => $pass,
			'feature' => '',
			'secid' => $uid,
			'user' => $user,
			'link_name' => url_title(basename($file)),
			'remote' => $remote_upload,
			'time' => time()
		);
		$this->db->insert('refrence', $data);
		return $file_id;
	}
	
	//------------------------
	// File Delete 
	//------------------------
	
	public function deleteFile($id, $secid, $name)
	{
		$fid = $this->db->select('link_id')->get_where('refrence', array('file_id' => $id, 'secid' => $secid, 'link_name' => $name));
		$file = $fid->row();
		
		$files = $this->db->get_where('refrence', array('link_id' => $file->link_id))->num_rows();
		if($files == 1)
		{
			$realfile = $this->db->get_where('files', array('id' => $file->link_id))->row();
			$this->db->delete('files', array('id' => $realfile->id));
		}
		$this->db->delete('refrence', array('file_id' => $id));
	}
	
	public function deleteFileAdmin($id)
	{
		$fid = $this->db->select('link_id')->join('files', 'refrence.link_id = files.id')->get_where('refrence', array('file_id' => $id));
		if($fid->num_rows() >= 1)
		{
			$file = $fid->row();
			
			$files = $this->db->get_where('refrence', array('link_id' => $file->link_id))->num_rows();
			if($files == 1)
			{
				$realfile = $this->db->get_where('files', array('id' => $file->link_id))->row();
				$this->db->delete('files', array('id' => $realfile->id));
			}
			$this->db->delete('refrence', array('file_id' => $id));
		}
	}
	
	public function banFileAdmin($id)
	{
		$file = $this->_getFileObject($id);
		if(!$file)
		{
			echo $file;
			return false;	
		}
		$this->db->delete('refrence', array('link_id' => $file->link_id));	
		$this->db->delete('files', array('md5' => $file->md5));
		$this->db->insert('bans', array('md5' => $file->md5, 'name' => $file->o_filename));
	}
	
	public function addToDownloads($id)
	{
		$fid = $this->db->select('downloads')->get_where('refrence', array('file_id' => $id));
		$file = $fid->row();
		
		$data = array(
		   'downloads' => $file->downloads + 1,
		   'last_download' => time()
		);

		$this->db->where('file_id', $id);
		$this->db->update('refrence', $data); 
	}
	
	public function editPremiumBandwith($id, $ammount, $previous, $plus=false)
	{
		if($plus)
		{
			$data['direct_bw'] = ($previous + $ammount);
		}
		else
		{
			$data['direct_bw'] = ($previous - $ammount);
		}
		
		if($data['direct_bw'] < 0)
		{
			$data['direct_bw'] = 0;
			$data['direct'] = 0;
		}

		$this->db->where('file_id', $id);
		$this->db->update('refrence', $data); 
	}
	
	public function _getFileObject($id, $select='', $where=array())
	{
		if($select != '')
		{
			$this->db->select($select);
		}
		
		$sql_where = array_merge($where,  array('file_id' => $id));
		
		$query = $this->db->join('files', 'refrence.link_id = files.id')->get_where('refrence', $sql_where, 1, 0);
		if($query->num_rows() >= 1)
		{
			return $query->row();
		}
		else
		{
			return false;
		}
	}
}
?>