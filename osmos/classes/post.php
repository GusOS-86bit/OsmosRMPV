<?php
class Post {
    private $error = "";

    private $allowed_uploads = [
        "image/jpeg" => "jpg",
        "video/mp4" => "mp4",
    ];

    public function create_post($user_id, $data, $files, $owner = 0) {
        $this->error = "";

        if (!is_numeric($user_id) || $user_id <= 0) {
            return "You must be logged in to post!<br>";
        }

        $text = (isset($data['post']) && is_string($data['post'])) ? $data['post'] : "";

        // change_profile_image.php passes the file path as a string in $files
        // a real upload is always an array, so this can't be faked
        $is_image_post = is_string($files) && $files !== "" && (isset($data['is_profile_image']) || isset($data['is_cover_image']));
        $has_upload = is_array($files) && isset($files['file']) && !empty($files['file']['name']);

        if (trim($text) === "" && !$has_upload && !$is_image_post) {
            return "Please type something to post! <br>";
        }

        $myimage = "";
        $has_image = 0;
        $is_cover_image = 0;
        $is_profile_image = 0;

        if ($is_image_post) {
            $myimage = $files;
            $has_image = 1;
            if (isset($data['is_cover_image'])) $is_cover_image = 1;
            if (isset($data['is_profile_image'])) $is_profile_image = 1;
        } elseif ($has_upload) {
            $upload = store_upload($files['file'], $user_id, $this->allowed_uploads);
            if ($upload['error'] != "") {
                $this->error .= $upload['error'];
                return $this->error;
            }
            $myimage = $upload['path'];
            $has_image = 1;
        }

        $parent = 0;
        $parent_post = false;
        if (isset($data['parent']) && is_numeric($data['parent']) && $data['parent'] > 0) {
            $parent_post = $this->get_one_post($data['parent']);
            if (!$parent_post) {
                return "The post you are replying to no longer exists!<br>";
            }
            $parent = $parent_post['post_id'];
        }

        $post_id = $this->create_postid();

        $DB = new Database();
        $safe_user = esc($user_id);
        $safe_post = esc($text);
        $safe_image = esc($myimage);
        $safe_parent = esc($parent);
        $safe_owner = esc(is_numeric($owner) ? $owner : 0);

        $query = "insert into posts (post_id, user_id, post, image, has_image, is_cover_image, is_profile_image, parent, owner) values ('$post_id', '$safe_user', '$safe_post', '$safe_image', '$has_image', '$is_cover_image', '$is_profile_image', '$safe_parent', '$safe_owner')";
        if (!$DB->save($query)) {
            return "Your post could not be saved, please try again.<br>";
        }

        // notify @mentions
        $tags = array();
        preg_match_all(MENTION_PATTERN, $text, $tags);

        if (isset($tags[2]) && is_array($tags[2])) {
            foreach (array_unique($tags[2]) as $tag) {
                $tag = esc($tag);
                $sql = "select userid from users where tag_name = '$tag' and (type is null or type = '' or type = 'profile') limit 1";
                $tagged_user = $DB->read($sql);

                if (is_array($tagged_user)) {
                    $tagged_userid = $tagged_user[0]['userid'];
                    $tag_post = array('userid' => $tagged_userid, 'postid' => $post_id);
                    if ($tagged_userid != $user_id) {
                        add_notification($user_id, "tag", $tag_post);
                    }
                }
            }
        }

        if ($parent > 0) {
            $sql = "update posts set comments = comments + 1 where post_id = '$safe_parent' limit 1";
            $DB->save($sql);

            // followers get it through content_i_follow
            if ($parent_post['user_id'] != $user_id) {
                add_notification($user_id, "comment", $parent_post);
            }

            // commenting on a post follows it
            content_i_follow($user_id, $parent_post);
        } else {
            content_i_follow($user_id, ['postid' => $post_id, 'user_id' => $user_id]);
        }

        return $this->error;
    }

    // $post has to be htmlspecialchars'd already
    public function get_tags($post) {
        $DB = new Database();
        $cache = [];

        // @mentions
        $result = preg_replace_callback(MENTION_PATTERN, function ($m) use ($DB, &$cache) {
            $tag = $m[2];
            if (!array_key_exists($tag, $cache)) {
                $safe = esc($tag);
                $found = $DB->read("select userid, type from users where tag_name = '$safe' limit 1");
                $cache[$tag] = is_array($found) ? $found[0] : false;
            }

            if ($cache[$tag]) {
                $path = (($cache[$tag]['type'] ?? "") == "group" ? "group/" : "profile/") . $cache[$tag]['userid'];
                return $m[1] . "<a href='" . ROOT . $path . "' class='tag-link'>@" . $tag . "</a>";
            }
            return $m[0];
        }, $post);

        if ($result !== null) {
            $post = $result;
        }

        // #hashtags
        $result = preg_replace_callback(HASHTAG_PATTERN, function ($m) {
            return $m[1] . "<a href='" . ROOT . "tags?tag=" . urlencode($m[2]) . "' class='tag-link'>#" . $m[2] . "</a>";
        }, $post);

        if ($result !== null) {
            $post = $result;
        }

        return $post;
    }

    public function get_post($id, $post_type = "profile") {
        $DB = new Database();
        $id = esc($id);

        $query = "SELECT * FROM posts WHERE parent = 0 AND user_id = '$id' AND owner = 0 ORDER BY id DESC LIMIT 10";

        if($post_type == "group") {
            $query = "SELECT * FROM posts WHERE parent = 0 AND owner = '$id' ORDER BY id DESC LIMIT 10";
        }

        $data = $DB->read($query);
        return is_array($data) ? $data : false;
    }

    public function get_one_post($postid) {
        if(!is_numeric($postid)) return false;
        $DB = new Database();
        $postid = esc($postid);
        $result = $DB->read("select * from posts where post_id = '$postid' limit 1");
        if(is_array($result)) return $result[0];
        return false;
    }

    public function get_comments($parent_id) {
        if (!is_numeric($parent_id)) return false;
        $DB = new Database();
        $parent_id = esc($parent_id);
        $result = $DB->read("select * from posts where parent = '$parent_id' order by id asc");
        return $result ? $result : false;
    }

    // also deletes replies, likes, notifications, follows and the file
    public function delete_post($postid, $depth = 0) {
        if(!is_numeric($postid) || $depth > 50) return;

        $row = $this->get_one_post($postid);
        if (!$row) return;

        $DB = new Database();
        $id = esc($row['post_id']);

        // replies first, recursively
        $children = $DB->read("select post_id from posts where parent = '$id'");
        if (is_array($children)) {
            foreach ($children as $child) {
                $this->delete_post($child['post_id'], $depth + 1);
            }
        }

        $DB->save("delete from posts where post_id = '$id' limit 1");

        delete_upload_if_unused($row['image'] ?? "", $row['post_id']);

        $DB->save("delete from likes where contentid = '$id' and (type = 'post' or type = 'comment')");
        $DB->save("delete from notifications where content_id = '$id' and (content_type = 'post' or content_type = 'comment')");
        $DB->save("delete from content_i_follow where content_id = '$id' and (content_type = 'post' or content_type = 'comment')");

        if ($row['parent'] > 0) {
            $parent = esc($row['parent']);
            $DB->save("update posts set comments = GREATEST(comments - 1, 0) where post_id = '$parent' limit 1");
        }
    }

    public function i_own_post($post_id, $osmos_userid) {
        if(!is_numeric($post_id)) return false;
        $DB = new Database();
        $post_id = esc($post_id);
        $result = $DB->read("select * from posts where post_id = '$post_id' limit 1");
        if(is_array($result) && $result[0]['user_id'] == $osmos_userid) return true;
        return false;
    }

    // author, parent post's author, group mods/admins/owner and site admins
    public function can_delete_post($row, $user_id) {
        if (empty($user_id) || !is_array($row)) return false;

        if ($row['user_id'] == $user_id) return true;

        if (is_admin($user_id)) return true;

        if ($row['parent'] > 0) {
            $parent = $this->get_one_post($row['parent']);
            if ($parent && $parent['user_id'] == $user_id) return true;
        }

        if ($row['owner'] > 0) {
            $group_class = new Group();
            $group = $group_class->get_group($row['owner']);
            if ($group && group_access($user_id, $group, 'moderator')) return true;
        }

        return false;
    }

    // group posts follow the group, profile posts the author's privacy setting
    public function can_view_post($row, $user_id) {
        static $profile_cache = [];

        if (!is_array($row)) return false;

        if (is_admin($user_id)) return true;

        if ($row['owner'] > 0) {
            $group_class = new Group();
            $group = $group_class->get_group($row['owner']);
            if ($group) {
                return group_can_view($user_id, $group);
            }
        }

        // comments follow their post
        if ($row['parent'] > 0) {
            $parent = $this->get_one_post($row['parent']);
            return $parent ? $this->can_view_post($parent, $user_id) : false;
        }

        $key = $row['user_id'] . "_" . $user_id;
        if (!array_key_exists($key, $profile_cache)) {
            $user_class = new User();
            $author = $user_class->get_user($row['user_id']);
            $profile_cache[$key] = $author ? $user_class->can_view_profile($author, $user_id) : false;
        }

        return $profile_cache[$key];
    }

    public function like_post($id, $type, $osmos_userid) {
        $targets = ['post' => ['table' => 'posts', 'column' => 'post_id'], 'comment' => ['table' => 'posts', 'column' => 'post_id'], 'user' => ['table' => 'users', 'column' => 'userid']];
        if(!isset($targets[$type]) || !is_numeric($id) || empty($osmos_userid)) return;

        $table = $targets[$type]['table'];
        $type_id = $targets[$type]['column'];
        $id = esc($id);
        $DB = new Database();
        $result = $DB->read("select likes from likes where type='$type' && contentid = '$id' limit 1");

        if(!empty($result)) {
            $likes = json_decode($result[0]['likes'], true);
            if(!is_array($likes)) $likes = [];
            $user_ids = array_column($likes, "user_id");

            if(!in_array($osmos_userid, $user_ids)) {
                $likes[] = ["user_id" => (string)$osmos_userid, "date" => date("Y-m-d H:i:s")];
                $likes_string = esc(json_encode(array_values($likes)));
                $DB->save("update likes set likes = '$likes_string' where type='$type' && contentid = '$id' limit 1");
                $DB->save("update $table set likes = likes + 1 where $type_id = '$id' limit 1");

                if ($type == "post" || $type == "comment") {
                    $single_post = $this->get_one_post($id);
                    add_notification($osmos_userid, "like", $single_post);
                }
            } else {
                $key = array_search($osmos_userid, $user_ids);
                unset($likes[$key]);
                $likes_string = esc(json_encode(array_values($likes)));
                $DB->save("update likes set likes = '$likes_string' where type='$type' && contentid = '$id' limit 1");
                $DB->save("update $table set likes = GREATEST(likes - 1, 0) where $type_id = '$id' limit 1");
            }
        } else {
            $arr2 = [];
            $arr2[] = ["user_id" => (string)$osmos_userid, "date" => date("Y-m-d H:i:s")];
            $likes = esc(json_encode($arr2));
            $DB->save("insert into likes (type, contentid, likes) values ('$type', '$id', '$likes')");
            $DB->save("update $table set likes = likes + 1 where $type_id = '$id' limit 1");

            if ($type == "post" || $type == "comment") {
                $single_post = $this->get_one_post($id);
                add_notification($osmos_userid, "like", $single_post);
            }
        }
    }

    public function get_likes($id, $type) {
        $DB = new Database();
        $type = esc($type);
        if(is_numeric($id)) {
            $id = esc($id);
            $result = $DB->read("select likes from likes where type='$type' && contentid = '$id' limit 1");
            if(is_array($result)) {
                $likes = json_decode($result[0]['likes'], true);
                return (is_array($likes) && count($likes) > 0) ? $likes : false;
            }
        }
        return false;
    }

    public function user_liked($id, $type, $user_id) {
        if (empty($user_id)) return false;
        $likes = $this->get_likes($id, $type);
        if (!is_array($likes)) return false;
        return in_array((string)$user_id, array_map('strval', array_column($likes, "user_id")), true);
    }

    private function create_postid() {
        return generate_unique_id("posts", "post_id");
    }

    // Photos tab, no group posts or videos
    public function get_images($id) {
        $id = esc($id);
        $DB = new Database();
        $result = $DB->read("select * from posts where has_image = 1 && user_id = '$id' && owner = 0 && image NOT LIKE '%.mp4' order by id desc limit 30");
        return $result ? $result : false;
    }

    // $postid has to come from the url, never from the form
    public function edit_post($postid, $data, $files) {
        $this->error = "";

        $row = $this->get_one_post($postid);
        if (!$row) {
            return "No such post was found!<br>";
        }

        $text = (isset($data['post']) && is_string($data['post'])) ? $data['post'] : "";

        $upload = store_upload($files['file'] ?? null, $row['user_id'], $this->allowed_uploads);
        if ($upload['error'] != "") {
            $this->error .= $upload['error'];
            return $this->error;
        }

        if (trim($text) === "" && $upload['path'] === "" && empty($row['image'])) {
            return "Please type something to post! <br>";
        }

        $DB = new Database();
        $post = esc($text);
        $safe_id = esc($row['post_id']);

        if ($upload['path'] !== "") {
            $myimage = esc($upload['path']);
            $query = "update posts set post = '$post', image = '$myimage', has_image = '1' where post_id = '$safe_id' limit 1";
            $DB->save($query);

            delete_upload_if_unused($row['image'] ?? "", $row['post_id']);
        } else {
            $query = "update posts set post = '$post' where post_id = '$safe_id' limit 1";
            $DB->save($query);
        }

        return $this->error;
    }
}
