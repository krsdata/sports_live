<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class CommonModel extends CI_Model
{
    function __construct() 
    {
        parent::__construct();
    }

    public function insertData($tbl_name, $data)
    {
        $this->db->insert($tbl_name, $data);
        $insert_id = $this->db->insert_id();

        return $insert_id;
    }

    public function updateData($tbl_name, $data, $where)
    {
        $this->db->where($where);
        $this->db->update($tbl_name, $data);       

        $isDbError = $this->dbError();
        if (isset($isDbError['db_error'])) 
            return $isDbError;
    }

    public function selectRecord($where = array(), $tbl_name, $columns="*")
    {
        if (count($where)>0) 
            $this->db->where($where);

        $this->db->select($columns, False);

        $query = $this->db->get($tbl_name);
        $this->dbError();

        return $query->row_array();
    }

    public function selectRecords($where = array(), $tbl_name, $columns, $order_by = array(), $where_in = '', $where_not_in = '', $limit = '')
    {   
        if ($where != "" && count($where)>0) 
            $this->db->where($where);

        if (!empty($where_in)) 
            $this->db->where_in($where_in[0], $where_in[1]);       

        if (!empty($where_not_in)) 
        {
            if (count($where_not_in[1])>0)
                $this->db->where_not_in($where_not_in[0], $where_not_in[1]);
        }

        $this->db->select($columns, False);

        if (count($order_by)>0)
        {
            foreach ($order_by as $key => $value) 
                $this->db->order_by($key, $value);
        }       

        if ($limit) 
            $this->db->limit($limit[0], $limit[1]);

        $query = $this->db->get($tbl_name);

        return $query->result_array();
    }

    public function deleteRecord($tbl_name, $where)
    {
        $this->db->where($where);
        $this->db->delete($tbl_name);         

        $this->dbError();

        return TRUE;
    }

    public function dbError()
    {
        try 
        {
            $db_error = $this->db->error();

            if ($db_error['message']) 
                throw new Exception();
            else
                return TRUE;
        } 
        catch (Exception $e) 
        {
            $arrayResponse['status'] = FALSE;
            $arrayResponse['code'] = $db_error['message'];
            $arrayResponse['message'] = str_replace("'", "", $db_error['code']);

            echo json_encode($arrayResponse);
            die;
        }
    }
}
