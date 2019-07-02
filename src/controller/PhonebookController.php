<?php
namespace Src\Controller;

require "../src/model/phonebook.php";
require "../src/helpers/uploadImage.php";

use Src\Model\phonebook;
use Src\Helpers\uploadImage;

class PhonebookController {

    private $db;
    private $action;
    private $params;

    private $phonebook;
    private $uploadImage;

    public function __construct($db, $action, $params)
    {
        $this->db = $db;
        $this->action = $action;
        $this->params = $params;
        $this->recordId = null;;
        $this->phonebook = new phonebook($db);
        $this->uploadImage = new uploadImage();
    }

    public function processRequest()
    {
        if(is_int($this->params)) {
            $this->recordId = $this->params;
        }
        switch ($this->action) {
            case 'POST':
                $response = $this->createRecordFromRequest();
                break;
            case 'GET':
                if ($this->params) {
                    $response = $this->getRecord($this->params);
                } else {
                    $response = $this->getAllRecords();
                };
                break;
            case 'PUT':
                $response = $this->updateRecordFromRequest($this->recordId);
                break;
            case 'DELETE':
                $response = $this->deleteRecord($this->recordId);
                break;
            default:
                $response = $this->notFoundResponse();
                break;
        }
        header($response['status_code_header']);
        if ($response['body']) {
            echo $response['body'];
        }
    }

    private function getAllRecords()
    {
        $result = $this->phonebook->findAll();
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function getRecord($param)
    {
        if(is_int($param)) {
            $result = $this->phonebook->find($id);
        } else if(isset($param['field']) && isset($param['value'])) {
            $result = $this->phonebook->findByAttr($param);
        }
        if (! $result) {
            return $this->notFoundResponse();
        }
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = json_encode($result);
        return $response;
    }

    private function createRecordFromRequest()
    {
        $input = $_POST;
        if($input['_method'] === "PUT") {
            return $this->updateRecordFromRequest($this->recordId);
        }
        $img_response = $this->uploadImage->upload();
        if(isset($img_response['url'])) {
            $input['picture'] = $img_response['url'];
        }
        if (! $this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $data_response = $this->phonebook->insert($input);
        $response['status_code_header'] = 'HTTP/1.1 201 Created';
        $result['data'] = $data_response ;
        $result['image'] = $img_response['status'];
        $response['body'] = json_encode($result);
        return $response;
    }

    private function updateRecordFromRequest($id)
    {
        $result = $this->phonebook->find($id);

        if (! $result) {
            return $this->notFoundResponse();
        }
        $input = $_POST;
        $img_response = $this->uploadImage->upload();

        if(isset($img_response['url'])) {
            $input['picture'] = $img_response['url'];
        }
        if (! $this->validatePerson($input)) {
            return $this->unprocessableEntityResponse();
        }
        $result = $this->phonebook->update($id, $input);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body']['data'] = $result;
        $response['body']['image'] = $img_response['status'];
        echo json_encode($response);
    }

    private function deleteRecord($id)
    {
        $result = $this->phonebook->find($id);
        if (! $result) {
            return $this->notFoundResponse();
        }
        $this->phonebook->delete($id);
        $response['status_code_header'] = 'HTTP/1.1 200 OK';
        $response['body'] = null;
        return $response;
    }

    private function validatePerson($input)
    {
        if (! isset($input['firstname'])) {
            return false;
        }
        if (! isset($input['surname'])) {
            return false;
        }
        return true;
    }

    private function unprocessableEntityResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
        $response['body'] = json_encode([
            'error' => 'Invalid input'
        ]);
        return $response;
    }

    private function notFoundResponse()
    {
        $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
        $response['body'] = "person not found";
        return $response;
    }

}