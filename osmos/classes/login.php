<?php

class Login
{
    private $error = "";

    public function evaluate($data)
    {
        $username = isset($data['username']) && is_string($data['username']) ? strtolower(trim(ltrim(trim($data['username']), "@"))) : "";
        $password = isset($data['password']) && is_string($data['password']) ? $data['password'] : "";

        // groups have an empty password, so never let an empty one through
        if ($username === "" || $password === "") {
            $this->error .= "Please enter your username and password!<br>";
            return $this->error;
        }

        $username = esc($username);

        // profiles only
        $query = "select * from users where tag_name = '$username' and (type is null or type = '' or type = 'profile') limit 1";

        $DB = new Database();
        $result = $DB->read($query);

        if ($result)
        {
            $row = $result[0];
            $stored = (string)$row['password'];

            $authenticated = false;
            $needs_upgrade = false;

            if ($stored !== "") {
                $info = password_get_info($stored);

                if (!empty($info['algo'])) {
                    $authenticated = password_verify($password, $stored);
                    $needs_upgrade = $authenticated && password_needs_rehash($stored, PASSWORD_DEFAULT);
                } else {
                    // old accounts: sha256, sha3-256 or plain text, rehashed below
                    $authenticated =
                        hash_equals($stored, hash("sha256", $password)) ||
                        hash_equals($stored, hash("sha256", addslashes($password))) ||
                        hash_equals($stored, hash("sha3-256", $password)) ||
                        hash_equals($stored, $password);
                    $needs_upgrade = $authenticated;
                }
            }

            if ($authenticated && !empty($row['blocked']))
            {
                $this->error .= "This account has been blocked by an administrator.<br>";
                return $this->error;
            }

            if ($authenticated)
            {
                // upgrade old hashes
                if ($needs_upgrade) {
                    $userid = esc($row['userid']);
                    $new_hash = esc(password_hash($password, PASSWORD_DEFAULT));
                    $DB->save("UPDATE users SET password = '$new_hash' WHERE userid = '$userid' LIMIT 1");
                }

                // against session fixation
                session_regenerate_id(true);
                $_SESSION['osmos_userid'] = $row['userid'];
            }
            else
            {
                $this->error .= "Wrong username or password!<br>";
            }
        }
        else
        {
            $this->error .= "Wrong username or password!<br>";
        }

        return $this->error;
    }

    public function check_login($id, $redirect = true)
    {
        if(isset($_SESSION['osmos_userid']))
        {
            $userid = $_SESSION['osmos_userid'];

            if(is_numeric($id) && $id > 0)
            {
                $userid = $id;
            }

            $userid = esc($userid);
            $query = "select * from users where userid = '$userid' limit 1";

            $DB = new Database();
            $result = $DB->read($query);

            // kick out blocked users, groups should never have a session anyway
            if ($result && $result[0]['userid'] == $_SESSION['osmos_userid'] && (($result[0]['type'] ?? "") == "group" || !empty($result[0]['blocked'])))
            {
                unset($_SESSION['osmos_userid']);
                $result = false;
            }

            if ($result)
            {
                return $result[0];
            }
        }

        if($redirect)
        {
            header("Location: " . ROOT . "login");
            die;
        }
        else
        {
            return false;
        }
    }
}
