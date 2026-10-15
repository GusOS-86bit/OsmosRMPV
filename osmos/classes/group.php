<?php
class Group
{
    private $error = "";

    // set by evaluate()
    public $created_id = "";

    private $roles = ["member", "moderator", "admin"];

    public function evaluate($data)
    {
        $this->error = "";

        $name = (isset($data['group_name']) && is_string($data['group_name'])) ? trim($data['group_name']) : "";
        $about = (isset($data['about']) && is_string($data['about'])) ? trim($data['about']) : "";
        $group_type = (isset($data['group_type']) && $data['group_type'] == "private") ? "private" : "public";

        if ($name === "") {
            $this->error .= "Group name is empty!<br>";
        } elseif (is_numeric($name)) {
            $this->error .= "Group name cannot be a number.<br>";
        } elseif (mb_strlen($name) > 50) {
            $this->error .= "Group name is too long (max 50 characters).<br>";
        }

        if (mb_strlen($about) > 1024) {
            $this->error .= "The description is too long (max 1024 characters).<br>";
        }

        if ($this->error != "") {
            return $this->error;
        }

        $id = $this->create_group([
            "group_name" => $name,
            "about" => $about,
            "group_type" => $group_type,
        ]);

        if (!$id) {
            return "The group could not be created, please try again.<br>";
        }

        $this->created_id = $id;
        return "";
    }

    public function create_group($data)
    {
        $owner = $_SESSION['osmos_userid'] ?? 0;
        if (empty($owner)) {
            return false;
        }

        $first_name = osmos_ucfirst(trim($data['group_name']));
        $userid = $this->create_userid();

        $clean_name = str_replace("_", "", ascii_slug($first_name));
        if ($clean_name == "") {
            $clean_name = "group";
        }
        // tag_name is unique, so keep trying until we get a free one
        $DB = new Database();
        do {
            $tag_name = $clean_name . random_int(0, 9999);
            $taken = $DB->read("SELECT id FROM users WHERE tag_name = '" . esc($tag_name) . "' LIMIT 1");
        } while (!empty($taken));

        $type = "group";
        $group_type = (($data['group_type'] ?? "public") == "private") ? "private" : "public";
        $about = $data['about'] ?? "";
        $date = date("Y-m-d H:i:s");

        $first_name = esc($first_name);
        $tag_name = esc($tag_name);
        $about = esc($about);
        $owner = esc($owner);

        $query = "INSERT INTO users
        (userid, owner, first_name, last_name, gender, password, tag_name, date, type, group_type, about)
        VALUES
        ('$userid', '$owner', '$first_name', '', '', '', '$tag_name', '$date', '$type', '$group_type', '$about')";

        $DB = new Database();
        return $DB->save($query) ? $userid : false;
    }

    private function create_userid()
    {
        return generate_unique_id("users", "userid");
    }

    // owned + joined groups, no duplicates
    public function get_my_groups($owner)
    {
        $DB = new Database();
        $owner = esc($owner);
        $result = [];

        $result_owned = $DB->read("SELECT * FROM users WHERE owner = '$owner' AND type = 'group' ORDER BY id DESC");
        if (is_array($result_owned)) {
            foreach ($result_owned as $row) {
                $result[$row['userid']] = $row;
            }
        }

        $result2 = $DB->read("SELECT groupid FROM group_members WHERE userid = '$owner' AND disabled = 0");
        if (is_array($result2)) {
            $group_ids = array_map('esc', array_column($result2, 'groupid'));
            $group_ids_string = "'" . implode("','", $group_ids) . "'";

            $group_rows = $DB->read("SELECT * FROM users WHERE userid IN ($group_ids_string) AND type = 'group' ORDER BY id DESC");
            if (is_array($group_rows)) {
                foreach ($group_rows as $row) {
                    if (!isset($result[$row['userid']])) {
                        $result[$row['userid']] = $row;
                    }
                }
            }
        }

        return count($result) > 0 ? array_values($result) : false;
    }

    public function get_group($id)
    {
        if (!is_numeric($id)) {
            return false;
        }

        $DB = new Database();
        $id = esc($id);
        $query = "SELECT * FROM users WHERE userid = '$id' AND type = 'group' LIMIT 1";
        $data = $DB->read($query);

        if (is_array($data)) {
            return $data[0];
        }
        return false;
    }

    // public: join right away, private: send a request, unless you were invited
    // returns "joined", "requested", "member" or false
    public function join_group($group_id, $user_id)
    {
        $group = $this->get_group($group_id);
        if (!$group || empty($user_id)) {
            return false;
        }

        if (group_access($user_id, $group, 'member')) {
            return "member";
        }

        if (($group['group_type'] ?? "public") != "private" || $this->is_invited($group_id, $user_id)) {
            $this->add_member($group_id, $user_id);
            return "joined";
        }

        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $query = "SELECT id FROM group_requests WHERE userid = '$user_id' AND groupid = '$group_id' LIMIT 1";
        $check = $DB->read($query);

        if (is_array($check)) {
            $id = $check[0]['id'];
            $query = "UPDATE group_requests SET disabled = 0 WHERE id = '$id' LIMIT 1";
            $DB->save($query);
        } else {
            $query = "INSERT INTO group_requests (groupid, userid, disabled) VALUES ('$group_id', '$user_id', 0)";
            $DB->save($query);
        }

        return "requested";
    }

    // also re-adds old members and closes their request/invite
    public function add_member($group_id, $user_id, $role = "member")
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);
        $role = in_array($role, $this->roles) ? $role : "member";

        $check = $DB->read("SELECT id FROM group_members WHERE userid = '$user_id' AND groupid = '$group_id' LIMIT 1");

        if (is_array($check)) {
            $id = $check[0]['id'];
            $DB->save("UPDATE group_members SET disabled = 0, role = '$role' WHERE id = '$id' LIMIT 1");
        } else {
            $DB->save("INSERT INTO group_members (groupid, userid, role, disabled) VALUES ('$group_id', '$user_id', '$role', 0)");
        }

        $DB->save("UPDATE group_requests SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'");
        $DB->save("UPDATE group_invites SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'");
    }

    // the owner can't leave
    public function leave_group($group_id, $user_id)
    {
        $group = $this->get_group($group_id);
        if (!$group || $group['owner'] == $user_id) {
            return false;
        }

        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);
        $DB->save("UPDATE group_members SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'");
        return true;
    }

    public function get_requests($group_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);

        $query = "SELECT * FROM group_requests WHERE groupid = '$group_id' AND disabled = 0 ORDER BY id ASC";
        $check = $DB->read($query);

        if (is_array($check)) {
            return $check;
        }
        return false;
    }

    public function has_request($group_id, $user_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $check = $DB->read("SELECT id FROM group_requests WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 LIMIT 1");
        return !empty($check);
    }

    public function accept_request($group_id, $user_id, $action)
    {
        if ($action == "accept") {
            $this->add_member($group_id, $user_id);
            return;
        }

        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $DB->save("UPDATE group_requests SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'");
        $DB->save("UPDATE group_invites SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'");
    }

    // owner first, then admins, mods, members
    public function get_members($group_id, $limit = 100)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $limit = (int)$limit;

        $result = [];
        $owner = 0;

        $check1 = $DB->read("SELECT owner FROM users WHERE userid = '$group_id' AND type = 'group' LIMIT 1");
        if (is_array($check1)) {
            $owner = $check1[0]['owner'];
            $result[] = ["userid" => $owner, "role" => "owner", "groupid" => $group_id];
        }

        $check2 = $DB->read("SELECT * FROM group_members WHERE groupid = '$group_id' AND disabled = 0 ORDER BY FIELD(role, 'admin', 'moderator', 'member'), id ASC LIMIT $limit");
        if (is_array($check2)) {
            foreach ($check2 as $row) {
                if ($row['userid'] != $owner) {
                    $result[] = $row;
                }
            }
        }

        return count($result) > 0 ? $result : false;
    }

    // owner + members
    public function count_members($group_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);

        $group = $DB->read("SELECT owner FROM users WHERE userid = '$group_id' AND type = 'group' LIMIT 1");
        if (!is_array($group)) {
            return 0;
        }
        $owner = esc($group[0]['owner']);

        $count = $DB->read("SELECT COUNT(id) AS total FROM group_members WHERE groupid = '$group_id' AND disabled = 0 AND userid != '$owner'");
        return 1 + (is_array($count) ? (int)$count[0]['total'] : 0);
    }

    // friends who aren't in the group yet
    public function get_invites($group_id, $me)
    {
        $user_class = new User();
        $friends = $user_class->get_friends($me);
        if (!is_array($friends)) {
            return false;
        }

        $members = $this->get_members($group_id, 10000);
        $member_ids = is_array($members) ? array_map('strval', array_column($members, 'userid')) : [];

        $result = [];
        foreach ($friends as $friend) {
            if (!in_array((string)$friend['userid'], $member_ids, true) && empty($friend['blocked'])) {
                $result[] = $friend;
            }
        }

        return count($result) > 0 ? $result : false;
    }

    public function invite_to_group($group_id, $user_id, $me)
    {
        $group = $this->get_group($group_id);
        $user_class = new User();
        $target = $user_class->get_user($user_id);

        // no groups and no existing members
        if (!$group || !is_array($target) || ($target['type'] ?? "") == "group" || group_access($user_id, $group, 'member')) {
            return false;
        }

        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);
        $me = esc($me);

        $query = "SELECT id FROM group_invites WHERE groupid = '$group_id' AND userid = '$user_id' LIMIT 1";
        $check = $DB->read($query);

        if (is_array($check)) {
            $id = $check[0]['id'];
            $query = "UPDATE group_invites SET disabled = 0, inviter = '$me' WHERE id = '$id' LIMIT 1";
            $DB->save($query);
        } else {
            $query = "INSERT INTO group_invites (groupid, userid, inviter, disabled) VALUES ('$group_id', '$user_id', '$me', 0)";
            $DB->save($query);
        }

        $group['target_user'] = $target['userid'];
        add_notification($me, "invite", $group);

        return true;
    }

    public function get_invited($group_id, $me)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $me = esc($me);

        $query = "SELECT * FROM group_invites WHERE groupid = '$group_id' AND userid = '$me' AND disabled = 0";
        $check = $DB->read($query);

        if (is_array($check)) {
            return $check;
        }
        return false;
    }

    public function is_invited($group_id, $user_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $query = "SELECT id FROM group_invites WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 LIMIT 1";
        $check = $DB->read($query);

        if (is_array($check)) {
            return true;
        }
        return false;
    }

    // the owner is in users.owner, so they can't get removed here
    public function remove_member($group_id, $user_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $query = "UPDATE group_members SET disabled = 1 WHERE userid = '$user_id' AND groupid = '$group_id'";
        $DB->save($query);
    }

    public function edit_member_access($group_id, $user_id, $role)
    {
        if (!in_array($role, $this->roles, true)) {
            return false;
        }

        $DB = new Database();
        $safe_group = esc($group_id);
        $safe_user = esc($user_id);
        $safe_role = esc($role);

        $query = "UPDATE group_members SET role = '$safe_role' WHERE userid = '$safe_user' AND groupid = '$safe_group' AND disabled = 0 LIMIT 1";
        $DB->save($query);

        $group_row = $this->get_group($group_id);
        if (is_array($group_row)) {
            $group_row['target_user'] = $user_id;
            add_notification($_SESSION['osmos_userid'] ?? 0, "role", $group_row);
        }

        return true;
    }

    // "owner", "admin", "moderator", "member" or ""
    public function get_member_role($group_id, $user_id)
    {
        $DB = new Database();
        $group_id = esc($group_id);
        $user_id = esc($user_id);

        $owner = $DB->read("SELECT id FROM users WHERE userid = '$group_id' AND owner = '$user_id' AND type = 'group' LIMIT 1");
        if (is_array($owner)) {
            return "owner";
        }

        $result = $DB->read("SELECT role FROM group_members WHERE userid = '$user_id' AND groupid = '$group_id' AND disabled = 0 LIMIT 1");
        if (is_array($result)) {
            return $result[0]['role'];
        }

        return "";
    }

    // admin panel only, deletes everything in the group
    public function delete_group($group_id)
    {
        $group = $this->get_group($group_id);
        if (!$group) {
            return false;
        }

        $DB = new Database();
        $id = esc($group['userid']);

        // delete_post() also cleans up comments, likes and files
        $post_class = new Post();
        $posts = $DB->read("SELECT post_id FROM posts WHERE owner = '$id' AND parent = 0");
        if (is_array($posts)) {
            foreach ($posts as $row) {
                $post_class->delete_post($row['post_id']);
            }
        }
        $DB->save("DELETE FROM posts WHERE owner = '$id'");

        $DB->save("DELETE FROM group_members WHERE groupid = '$id'");
        $DB->save("DELETE FROM group_requests WHERE groupid = '$id'");
        $DB->save("DELETE FROM group_invites WHERE groupid = '$id'");
        $DB->save("DELETE FROM notifications WHERE content_type = 'group' AND content_id = '$id'");
        $DB->save("DELETE FROM users WHERE userid = '$id' AND type = 'group' LIMIT 1");

        return true;
    }

    // returns "" or the errors
    public function save_settings($group_id, $data)
    {
        $name = (isset($data['group_name']) && is_string($data['group_name'])) ? trim($data['group_name']) : "";
        $about = (isset($data['about']) && is_string($data['about'])) ? trim($data['about']) : "";
        $group_type = $data['group_type'] ?? "";
        $error = "";

        if ($name === "") {
            $error .= "Group name is empty!<br>";
        } elseif (is_numeric($name)) {
            $error .= "Group name cannot be a number.<br>";
        } elseif (mb_strlen($name) > 50) {
            $error .= "Group name is too long (max 50 characters).<br>";
        }

        if (mb_strlen($about) > 1024) {
            $error .= "The description is too long (max 1024 characters).<br>";
        }

        if (!in_array($group_type, ["public", "private"], true)) {
            $error .= "Please choose who can see the group.<br>";
        }

        if ($error != "") {
            return $error;
        }

        $DB = new Database();
        $group_id = esc($group_id);
        $name = esc(osmos_ucfirst($name));
        $about = esc($about);
        $group_type = esc($group_type);

        $DB->save("UPDATE users SET first_name = '$name', about = '$about', group_type = '$group_type' WHERE userid = '$group_id' AND type = 'group' LIMIT 1");
        return "";
    }
}
