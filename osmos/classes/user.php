<?php

class User
{
    public function get_user($id)
    {
        if(!is_numeric($id)) {
            return false;
        }

        $id = esc($id);
        $query = "select * from users where userid = '$id' limit 1";
        $DB = new Database();
        $result = $DB->read($query);

        if(is_array($result) && isset($result[0])) {
            return $result[0];
        }

        return false;
    }

    // friends: one row per pair, status 'pending' or 'accepted'

    private function get_friend_row($user_a, $user_b)
    {
        $DB = new Database();
        $a = esc($user_a);
        $b = esc($user_b);

        $result = $DB->read("select * from friends where (sender = '$a' and receiver = '$b') or (sender = '$b' and receiver = '$a') limit 1");
        return is_array($result) ? $result[0] : false;
    }

    // "friends", "sent", "received" or "none"
    public function friendship_status($me, $other)
    {
        if (empty($me) || empty($other) || $me == $other) {
            return "none";
        }

        $row = $this->get_friend_row($me, $other);
        if (!$row) {
            return "none";
        }

        if ($row['status'] == "accepted") {
            return "friends";
        }

        return ($row['sender'] == $me) ? "sent" : "received";
    }

    public function are_friends($user_a, $user_b)
    {
        return $this->friendship_status($user_a, $user_b) == "friends";
    }

    // no groups, no blocked users, not yourself
    private function can_be_friend($me, $other)
    {
        if (empty($me) || !is_numeric($other) || $me == $other) {
            return false;
        }

        $row = $this->get_user($other);
        return is_array($row) && ($row['type'] ?? "") != "group" && empty($row['blocked']);
    }

    public function send_friend_request($me, $other)
    {
        if (!$this->can_be_friend($me, $other)) {
            return false;
        }

        $status = $this->friendship_status($me, $other);

        // they already asked me, so just accept
        if ($status == "received") {
            return $this->accept_friend_request($me, $other);
        }

        if ($status != "none") {
            return false;
        }

        $DB = new Database();
        $sender = esc($me);
        $receiver = esc($other);
        $date = date("Y-m-d H:i:s");
        $DB->save("insert into friends (sender, receiver, status, date) values ('$sender', '$receiver', 'pending', '$date')");

        add_notification($me, "friend_request", ["userid" => $other, "sender" => $me]);
        return true;
    }

    public function accept_friend_request($me, $other)
    {
        if ($this->friendship_status($me, $other) != "received") {
            return false;
        }

        $DB = new Database();
        $sender = esc($other);
        $receiver = esc($me);
        $date = date("Y-m-d H:i:s");
        $DB->save("update friends set status = 'accepted', date = '$date' where sender = '$sender' and receiver = '$receiver' limit 1");

        add_notification($me, "friend_accept", ["userid" => $other, "sender" => $me]);
        return true;
    }

    // used for decline, cancel and unfriend
    public function remove_friend_row($me, $other)
    {
        if ($this->friendship_status($me, $other) == "none") {
            return false;
        }

        $DB = new Database();
        $a = esc($me);
        $b = esc($other);
        $DB->save("delete from friends where (sender = '$a' and receiver = '$b') or (sender = '$b' and receiver = '$a')");

        $DB->save("delete from notifications where activity = 'friend_request' and ((user_id = '$a' and content_owner = '$b') or (user_id = '$b' and content_owner = '$a'))");
        return true;
    }

    public function get_friend_ids($id)
    {
        if (!is_numeric($id)) {
            return [];
        }

        $DB = new Database();
        $id = esc($id);
        $result = $DB->read("select sender, receiver from friends where status = 'accepted' and (sender = '$id' or receiver = '$id')");

        $ids = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                $ids[] = ($row['sender'] == $id) ? $row['receiver'] : $row['sender'];
            }
        }

        return $ids;
    }

    public function get_friends($id)
    {
        $ids = $this->get_friend_ids($id);
        if (count($ids) == 0) {
            return false;
        }

        $DB = new Database();
        $list = "'" . implode("','", array_map('esc', $ids)) . "'";
        $result = $DB->read("select * from users where userid in ($list) order by first_name, last_name");

        return is_array($result) ? $result : false;
    }

    public function count_friends($id)
    {
        return count($this->get_friend_ids($id));
    }

    public function get_received_requests($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        $DB = new Database();
        $id = esc($id);
        $result = $DB->read("select * from friends where receiver = '$id' and status = 'pending' order by id desc");

        return is_array($result) ? $result : false;
    }

    public function get_sent_requests($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        $DB = new Database();
        $id = esc($id);
        $result = $DB->read("select * from friends where sender = '$id' and status = 'pending' order by id desc");

        return is_array($result) ? $result : false;
    }

    public function count_received_requests($id)
    {
        $requests = $this->get_received_requests($id);
        return is_array($requests) ? count($requests) : 0;
    }

    // privacy 'friends' = only friends, the owner and admins

    public function can_view_profile($profile_row, $viewer_id)
    {
        if (!is_array($profile_row)) {
            return false;
        }

        if (($profile_row['privacy'] ?? "public") != "friends") {
            return true;
        }

        if (empty($viewer_id)) {
            return false;
        }

        if ($profile_row['userid'] == $viewer_id || is_admin($viewer_id)) {
            return true;
        }

        return $this->are_friends($profile_row['userid'], $viewer_id);
    }
}
