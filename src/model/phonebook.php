<?php
namespace Src\Model;

class phonebook {

    private $db = null;
    private $keys = array('emails', 'phones');
    private $db_table = array('emails' => 'email', 'phones' => 'phone');
    private $db_info = array(
            'firstname' => 'phone_book',
            'surname' => 'phone_book',
            'picture' => 'phone_book',
            'phone' => 'phone',
            'email' => 'email');

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findAll()
    {
        $statement = "
            SELECT 
                e.email,
                pb.id,
                pb.firstname,
                pb.surname,
                pb.picture
            FROM
                phone_book pb
            INNER JOIN email e ON e.person_id = pb.id
            UNION 
            SELECT 
                p.phone,
                pb.id,
                pb.firstname,
                pb.surname,
                pb.picture
            FROM
                phone_book pb
            INNER JOIN phone p ON p.person_id = pb.id";

        try {
            $statement = $this->db->query($statement);
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function find($id)
    {
        $statement = "
            SELECT 
                e.email,
                pb.id,
                pb.firstname,
                pb.surname,
                pb.picture
            FROM
                phone_book pb
            INNER JOIN email e ON e.person_id = pb.id
            WHERE pb.id = :id
            UNION 
            SELECT 
                p.phone,
                pb.id,
                pb.firstname,
                pb.surname,
                pb.picture
            FROM
                phone_book pb
            INNER JOIN phone p ON p.person_id = pb.id
            WHERE pb.id = :id";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function findByAttr($param)
    {
        switch($param['field']) {
            case 'id' :
            case 'firstname' :
            case 'surname' :
            case 'picture' :
                $statement = "
                    SELECT 
                        e.email,
                        pb.id,
                        pb.firstname,
                        pb.surname,
                        pb.picture
                    FROM
                        phone_book pb
                    INNER JOIN email e ON e.person_id = pb.id
                    WHERE pb.{$param['field']} LIKE :value
                    UNION 
                    SELECT 
                        p.phone,
                        pb.id,
                        pb.firstname,
                        pb.surname,
                        pb.picture
                    FROM
                        phone_book pb
                    INNER JOIN phone p ON p.person_id = pb.id
                    WHERE pb.{$param['field']} LIKE :value";
                break;
            case 'email':
            case 'phone':
                $statement = "
                    SELECT 
                        *
                    FROM
                        phone_book db
                    INNER JOIN {$param['field']} pb ON pb.person_id = db.id
                    WHERE pb.{$param['field']} LIKE :value";
                break;
            default: 
                return "not found";
                break;
            }
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('value' => '%'.$param['value'].'%'));
            return $statement->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insert(Array $input)
    {
        $statement = "
            INSERT INTO phone_book 
                (firstname, surname, picture)
            VALUES
                (:firstname, :surname, :picture);
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'firstname' => $input['firstname'],
                'surname'  => $input['surname'],
                'picture'  => $input['picture'],
            ));
            $recordId = $this->db->lastInsertId();
            $statement->closeCursor();
            foreach($this->keys as $key) {
                if(isset($input[$key])) {
                    $this->insertExtraInfo($input[$key], $recordId, $key);
                }
            }
            return 'success';
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function update($id, Array $input)
    {
        $statement = "
            UPDATE phone_book
            SET 
                firstname = :firstname,
                surname  = :surname,
                picture  = :picture
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array(
                'id' => (int) $id,
                'firstname' => $input['firstname'],
                'surname'  => $input['surname'],
                'picture'  => $input['picture']
            ));
            if($statement->rowCount()) {
                $statement->closeCursor();
                foreach($this->keys as $key) {
                    if(isset($input[$key])) {
                        $this->updateExtraInfo($id, $key, $input[$key]);
                    }
                }
            }
            return 'sucess';
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function updateExtraInfo($id, $db_table, Array $input)
    {
        $this->deleteExtraInfo($this->db_table[$db_table], $id);
        $this->insertExtraInfo($input, $id, $db_table);
    }

    public function delete($id)
    {
        $statement = "
            DELETE FROM phone_book
            WHERE id = :id;
        ";

        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('id' => $id));
            $statement->closeCursor();
            foreach($this->keys as $key) {
                $this->deleteExtraInfo($this->db_table[$key], $id);
            }
            return 'success';
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function deleteExtraInfo($db_table, $person_id)
    {
        $statement = "
            DELETE FROM $db_table
            WHERE person_id = :person_id;
        ";
        try {
            $statement = $this->db->prepare($statement);
            $statement->execute(array('person_id' => $person_id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }    
    }

    public function insertExtraInfo(Array $input, $recordId, $key)
    {
        $db_table = substr($key, 0, -1); 
        $statement = "
            INSERT INTO $db_table 
                (person_id, $db_table)
            VALUES
                (:person_id, :$db_table);
        ";

        try {
            $statement = $this->db->prepare($statement);
            foreach($input as $record) {
                $statement->execute(array(
                    'person_id' => $recordId,
                    "$db_table"  => $record,
                ));
            }
            return 'success';
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }
}