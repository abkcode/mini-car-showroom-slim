<?php
include __DIR__."/../Database.php";

class Manufacturer
{
    private $db;
    private $tblName = 'manufacturers';

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
        return $errors;
    }

    public function add($postData)
    {
        $valdationErrors = $this->validate($postData);
        if (!empty($valdationErrors)) {
            return [
                'code' => 422,
                'response' => ['message' => implode(' - ', $valdationErrors)]
            ];
        }
        $manufacturerData = array(
            'name' => $postData['name']
        );
        $result = $this->db->insert($this->tblName, $manufacturerData);
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
        $manufacturers = $this->db->select($this->tblName);
        if ($manufacturers === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to fetch']
            ];
        }
        return [
            'code' => 200,
            'response' => $manufacturers
        ];
    }
}
