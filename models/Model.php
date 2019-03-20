<?php
include "./Database.php";

class Model
{
    private $db;
    private $tblName = 'models';

    public function __construct()
    {
        $this->db = new Database();
    }

    private function validate($postData)
    {
        $errors = [];
        if (empty($postData)) {
            $errors[] = 'Invalid Request Payload';
            return $errors;
        }
        if (empty($postData['name'])) {
            $errors[] = 'name: should not be empty';
        }
        if (strlen($postData['name']) > 255) {
            $errors[] = 'name: should be less than 255 charaters';
        }
        if (empty($postData['manufacturer_id'])) {
            $errors[] = 'manufacturer: should not be empty';
        }
        return $errors;
    }

    public function add($postData)
    {
        $valdationErrors = $this->validate($postData);
        if (!empty($valdationErrors)) {
            return [
                'code' => 422,
                'response' => ['errors' => $valdationErrors]
            ];
        }
        $modelData = array(
            'name' => $postData['name'],
            'manufacturer_id' => $postData['manufacturer_id']
        );
        $result = $this->db->insert($this->tblName, $modelData);
        if ($result === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to add']
            ];
        }
        return [
            'code' => 200,
            'response' => ['id' => $result]
        ];
    }

    public function index()
    {
        $models = $this->db->select($this->tblName);
        if ($models === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to fetch']
            ];
        }
        return [
            'code' => 200,
            'response' => $models
        ];
    }
}
