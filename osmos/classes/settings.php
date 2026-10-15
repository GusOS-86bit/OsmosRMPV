<?php
class Settings {
    public function get_settings($id) {
        $DB = new Database();
        $id = esc($id);
        $sql = "select * from users where userid = '$id' limit 1";
        $result = $DB->read($sql);
        if(is_array($result) && !empty($result)) return $result[0];
        return false;
    }

    // returns "" or the errors
    public function save_settings($id, $data) {
        $DB = new Database();
        $error = "";

        $value = function ($key) use ($data) {
            return (isset($data[$key]) && is_string($data[$key])) ? trim($data[$key]) : "";
        };

        $first_name = $value('first_name');
        $last_name = $value('last_name');
        $gender = $value('gender');
        $about = $value('about');
        $username = strtolower($value('username'));
        $privacy = $value('privacy');
        // don't trim passwords
        $password = (isset($data['password']) && is_string($data['password'])) ? $data['password'] : "";
        $password2 = (isset($data['password2']) && is_string($data['password2'])) ? $data['password2'] : "";

        foreach (["First name" => $first_name, "Last name" => $last_name] as $label => $name) {
            if ($name === "") {
                $error .= $label . " can't be empty!<br>";
            } else {
                if (is_numeric($name)) $error .= $label . " can't be a number!<br>";
                if (strstr($name, " ")) $error .= $label . " can't have spaces!<br>";
                if (mb_strlen($name) > 50) $error .= $label . " is too long!<br>";
            }
        }

        $error .= username_error($username, $id);

        if (!in_array($privacy, ["public", "friends"])) {
            $error .= "Please choose who can see your posts!<br>";
        }

        if (!in_array($gender, ["Male", "Female"])) {
            $error .= "Please select a gender!<br>";
        }

        if (mb_strlen($about) > 1024) {
            $error .= "The About me text is too long (max 1024 characters)!<br>";
        }

        // empty = keep the old password
        if ($password !== "" || $password2 !== "") {
            if ($password !== $password2) {
                $error .= "The passwords do not match!<br>";
            } elseif (mb_strlen($password) < PASSWORD_MIN_LENGTH) {
                $error .= "The new password must be at least " . PASSWORD_MIN_LENGTH . " characters long!<br>";
            }
        }

        if ($error != "") {
            return $error;
        }

        // only these columns get updated
        $updates = [
            'first_name' => osmos_ucfirst($first_name),
            'last_name' => osmos_ucfirst($last_name),
            'gender' => $gender,
            'about' => $about,
            'tag_name' => $username,
            'privacy' => $privacy,
        ];

        if ($password !== "") {
            $updates['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $sql = "update users set ";
        foreach ($updates as $key => $val) {
            $sql .= $key . "='" . esc($val) . "',";
        }
        $sql = trim($sql, ",");
        $sql .= " where userid = '" . esc($id) . "' limit 1";

        $DB->save($sql);
        return "";
    }
}
