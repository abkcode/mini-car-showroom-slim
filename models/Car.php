<?php
include __DIR__."/../Database.php";

class Car
{
    private $db;
    private $tblName = 'cars';

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
        if (empty($postData['model_id'])) {
            $errors[] = 'manufacturer: should not be empty';
        }
        if (empty($postData['color'])) {
            $errors[] = 'color: should not be empty';
        }
        if (empty($postData['manufacturing_year'])) {
            $errors[] = 'manufacturing_year: should not be empty';
        }
        if (empty($postData['registration_number'])) {
            $errors[] = 'registration_number: should not be empty';
        }
        if (empty($postData['img1'])) {
            $errors[] = 'img1: should not be empty';
        }
        if (empty($postData['img2'])) {
            $errors[] = 'img2: should not be empty';
        }
        return $errors;
    }

    public function add()
    {
        $postData = json_decode(file_get_contents('php://input'), true);
        $valdationErrors = $this->validate($postData);
        if (!empty($valdationErrors)) {
            return [
                'code' => 422,
                'response' => ['errors' => $valdationErrors]
            ];
        }
        $carData = array(
            'model_id' => $postData['model_id'],
            'color' => $postData['color'],
            'manufacturing_year' => $postData['manufacturing_year'],
            'registration_number' => $postData['registration_number'],
            'note' => $postData['note'],
            'img1' => $postData['img1'],
            'img2' => $postData['img2'],
        );
        $result = $this->db->insert($this->tblName, $carData);
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

    public function index($getParams)
    {
        $conditions = [];
        if (!empty($getParams['model_id'])) {
            $conditions['where']['model_id'] = $getParams['model_id'];
        }
        $cars = $this->db->select($this->tblName, $conditions);
        if ($cars === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to fetch']
            ];
        }
        return [
            'code' => 200,
            'response' => $cars
        ];
    }

    public function dashboard()
    {
        $conditions = [
            'select' => 'manufacturers.name as manufacturer_name, models.id, models.name as model_name, count(cars.id) as count',
            'joins' => ['LEFT JOIN models on (models.id=cars.model_id)','LEFT JOIN manufacturers on (manufacturers.id=models.manufacturer_id)'],
            'group' => 'models.id',
        ];
        $data = $this->db->select($this->tblName, $conditions);
        if ($data === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to fetch']
            ];
        }
        return [
            'code' => 200,
            'response' => $data
        ];
    }

    public function delete($id)
    {
        $models = $this->db->delete($this->tblName, ['id' => $id]);
        if ($models === false) {
            return [
                'code' => 500,
                'response' => ['message' => 'Unable to delete']
            ];
        }
        return [
            'code' => 200,
            'response' => ['message' => 'deleted sucessfully']
        ];
    }

    private function validateFile($file)
    {
        $errors = [];
        if ($file->getError() !== UPLOAD_ERR_OK) {
            $errors[] = 'Unable to upload image';
            return $errors;
        }
        $allowedExtensions = ["jpg", "jpeg", "bmp", "gif", "png"];
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Allowed extensions '.implode(',', $allowedExtensions);
        }
        return $errors;
    }

    public function uploadFile($request, $directory)
    {
        $files = $request->getUploadedFiles();
        if (empty($files['img'])) {
            return [
                'code' => 422,
                'response' => ['errors' => ['Please select image.']]
            ];
        }
        $uploadedFile = $files['img'];
        $valdationErrors = $this->validateFile($uploadedFile);
        if (!empty($valdationErrors)) {
            return [
                'code' => 422,
                'response' => ['errors' => $valdationErrors]
            ];
        }
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
        $filename = sprintf('%s.%0.8s', bin2hex(random_bytes(8)), $extension);
        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
        return [
            'code' => 200,
            'response' => ['img_name' => $filename]
        ];
    }
}
