<?php
class Signup
{
    private $error = "";

    function evaluate($data)
    {
        $fields = [
            "first_name" => "First name",
            "last_name" => "Last name",
            "username" => "Username",
            "gender" => "Gender",
            "password" => "Password",
            "confirm_password" => "Confirm password",
        ];

        $clean = [];
        foreach ($fields as $key => $label)
        {
            $value = (isset($data[$key]) && is_string($data[$key])) ? trim($data[$key]) : "";
            // don't trim passwords
            if ($key == "password" || $key == "confirm_password") {
                $value = (isset($data[$key]) && is_string($data[$key])) ? $data[$key] : "";
            }
            $clean[$key] = $value;

            if ($value === "")
            {
                $this->error .= $label . " is empty!<br>";
            }
        }

        $clean['username'] = strtolower($clean['username']);
        if ($clean['username'] !== "") {
            $this->error .= username_error($clean['username']);
        }

        foreach (["first_name" => "First name", "last_name" => "Last name"] as $key => $label)
        {
            if ($clean[$key] === "") continue;

            if (is_numeric($clean[$key])) {
                $this->error .= $label . " can't be a number!<br>";
            }
            if (strstr($clean[$key], " ")) {
                $this->error .= $label . " can't have spaces!<br>";
            }
            if (mb_strlen($clean[$key]) > 50) {
                $this->error .= $label . " is too long!<br>";
            }
        }

        if ($clean['gender'] !== "" && !in_array($clean['gender'], ["Male", "Female"])) {
            $this->error .= "Please select a gender!<br>";
        }

        if ($clean['password'] !== "" && mb_strlen($clean['password']) < PASSWORD_MIN_LENGTH) {
            $this->error .= "The password must be at least " . PASSWORD_MIN_LENGTH . " characters long!<br>";
        }

        if ($clean['password'] !== "" && $clean['password'] !== $clean['confirm_password']) {
            $this->error .= "The passwords do not match!<br>";
        }

        if($this->error == "")
        {
            $this->create_user($clean);
            return "";
        }
        else
        {
            return $this->error;
        }
    }

    public function create_user($data)
    {
        $first_name = osmos_ucfirst($data['first_name']);
        $last_name = osmos_ucfirst($data['last_name']);
        $gender = $data['gender'];

        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        // username doubles as the @tag
        $tag_name = $data['username'];

        $userid = $this->create_userid();
        $date = date("Y-m-d H:i:s");

        // no pictures yet
        $profile_image = "";
        $cover_image = "";
        $type = "profile";

        $first_name = esc($first_name);
        $last_name = esc($last_name);
        $gender = esc($gender);
        $password = esc($password);
        $tag_name = esc($tag_name);

        $query = "insert into users
            (userid, first_name, last_name, gender, password, profile_image, cover_image, date, tag_name, type)
            values
            ('$userid', '$first_name', '$last_name', '$gender', '$password', '$profile_image', '$cover_image', '$date', '$tag_name', '$type')";

        $DB = new Database();
        $DB->save($query);
    }

    private function create_userid()
    {
        return generate_unique_id("users", "userid");
    }
}
