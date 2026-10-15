<?php

class Messages
{
    private $error = "";

    // images only, the chat shows attachments as pictures
    private $allowed_uploads = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
    ];

    public function send($data, $files, $receiver)
    {
        $this->error = "";
        $DB = new Database();

        $sender = $_SESSION['osmos_userid'] ?? 0;
        if (empty($sender)) {
            return "You must be logged in to send messages.<br>";
        }

        // not to groups or yourself
        $user_class = new User();
        $receiver_row = $user_class->get_user($receiver);
        if (!is_array($receiver_row) || ($receiver_row['type'] ?? "") == "group" || $receiver_row['userid'] == $sender) {
            return "You can't send a message to this user.<br>";
        }

        $message = (isset($data['message']) && is_string($data['message'])) ? trim($data['message']) : "";
        $file = "";

        $upload = store_upload($files['file'] ?? null, $sender, $this->allowed_uploads);
        if ($upload['error'] != "") {
            $this->error .= $upload['error'];
        }
        $file = $upload['path'];

        if ($message == "" && $file == "" && $this->error == "") {
            $this->error .= "Please type something to send.<br>";
        }

        if ($this->error == "") {
            $sender = esc($sender);
            $receiver = esc($receiver_row['userid']);

            // reuse the msgid if they already have a chat
            $msgid = $this->create_msgid(60);
            $query = "SELECT msgid FROM messages WHERE (sender = '$sender' AND receiver = '$receiver') OR (receiver = '$sender' AND sender = '$receiver') ORDER BY id DESC LIMIT 1";
            $old_thread = $DB->read($query);

            if (is_array($old_thread)) {
                $msgid = $old_thread[0]['msgid'];
            }

            $msgid = esc($msgid);
            $message = esc($message);
            $file = esc($file);

            $query = "INSERT INTO messages (sender, msgid, receiver, message, file) VALUES ('$sender', '$msgid', '$receiver', '$message', '$file')";
            $DB->save($query);
        }

        return $this->error;
    }

    public function has_thread($user_id)
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);
        $user_id = esc($user_id);

        $query = "SELECT id FROM messages WHERE
                  ((sender = '$me' AND receiver = '$user_id' AND deleted_sender = 0) OR
                  (receiver = '$me' AND sender = '$user_id' AND deleted_receiver = 0)) LIMIT 1";

        return !empty($DB->read($query));
    }

    public function read($user_id)
    {
        $DB = new Database();

        $me = esc($_SESSION['osmos_userid'] ?? 0);
        $user_id = esc($user_id);

        $query = "SELECT * FROM messages WHERE
                  ((sender = '$me' AND receiver = '$user_id' AND deleted_sender = 0) OR
                  (receiver = '$me' AND sender = '$user_id' AND deleted_receiver = 0))
                  ORDER BY id DESC LIMIT 20";
        $data = $DB->read($query);

        if (is_array($data)) {
            // newest at the bottom
            usort($data, function ($a, $b) {
                return $a['id'] <=> $b['id'];
            });

            $update_query = "UPDATE messages SET seen = 1 WHERE receiver = '$me' AND sender = '$user_id' AND seen = 0";
            $DB->save($update_query);
        }

        return $data;
    }

    // latest message per person, newest chat first
    public function read_threads()
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);

        $query = "SELECT * FROM messages
                  WHERE (sender = '$me' AND deleted_sender = 0) OR (receiver = '$me' AND deleted_receiver = 0)
                  ORDER BY id DESC LIMIT 1000";

        $data = $DB->read($query);
        if (!is_array($data)) {
            return false;
        }

        $threads = [];
        foreach ($data as $row) {
            $other = ($row['sender'] == $me) ? $row['receiver'] : $row['sender'];
            if (!isset($threads[$other])) {
                $threads[$other] = $row;
            }
            if (count($threads) >= 20) {
                break;
            }
        }

        return array_values($threads);
    }

    public function read_one($id)
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);
        $id = (int)$id;

        $query = "SELECT * FROM messages WHERE id = '$id' AND ((sender = '$me' AND deleted_sender = 0) OR (receiver = '$me' AND deleted_receiver = 0)) LIMIT 1";
        $data = $DB->read($query);

        if (is_array($data)) {
            return $data[0];
        }
        return false;
    }

    // last message in the chat, for the delete page
    public function read_one_thread($msgid)
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);
        $msgid = esc($msgid);

        $query = "SELECT * FROM messages WHERE msgid = '$msgid' AND ((sender = '$me' AND deleted_sender = 0) OR (receiver = '$me' AND deleted_receiver = 0)) ORDER BY id DESC LIMIT 1";
        $data = $DB->read($query);

        if (is_array($data)) {
            return $data[0];
        }
        return false;
    }

    public function delete_one($id)
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);
        $id = (int)$id;

        $query = "SELECT * FROM messages WHERE id = '$id' AND (sender = '$me' OR receiver = '$me') LIMIT 1";
        $data = $DB->read($query);

        if (is_array($data)) {
            $row = $data[0];
            if ($row['sender'] == $me) {
                $update_query = "UPDATE messages SET deleted_sender = 1 WHERE id = '$id' LIMIT 1";
            } else {
                $update_query = "UPDATE messages SET deleted_receiver = 1 WHERE id = '$id' LIMIT 1";
            }
            $DB->save($update_query);
        }
    }

    // only hides it for me, the other person still has the chat
    public function delete_one_thread($msgid)
    {
        $DB = new Database();
        $me = esc($_SESSION['osmos_userid'] ?? 0);

        $latest = $this->read_one_thread($msgid);
        if (!is_array($latest)) {
            return;
        }

        $other = esc(($latest['sender'] == $me) ? $latest['receiver'] : $latest['sender']);

        $DB->save("UPDATE messages SET deleted_sender = 1 WHERE sender = '$me' AND receiver = '$other'");
        $DB->save("UPDATE messages SET deleted_receiver = 1 WHERE receiver = '$me' AND sender = '$other'");
    }

    private function create_msgid($length)
    {
        $array = array(0,1,2,3,4,5,6,7,8,9,'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
        $text = "";
        $length = random_int(20, $length);
        for($i = 0; $i < $length; $i++){
            $random = random_int(0, 61);
            $text .= $array[$random];
        }
        return $text;
    }
}
