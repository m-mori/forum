<?php
// Copyright 2011 Toby Zerner, Simon Zerner
// This file is part of esoTalk. Please see the included license file for usage information.

if (!defined("IN_ESOTALK")) exit;

/**
 * The Session model represents the current session and the current user. It provides functions for manipluating
 * and managing the session and user, such as storing data, logging in and out, and validating tokens.
 *
 * @package esoTalk
 */
class ETSession extends ETModel {


/**
 * 最新のユーザ情報
 * An array of the current user's details, or null if they're not logged in.
 * @var array
 */
public $user;


/**
 * SWCのユーザID
 * The current user's member ID, or null if they're not logged in.
 * @var int
 */
public $userId;


/**
 * トークン TODO: 必要か？確認中
 * The current valid token.
 * @var string
 */
public $token;


/**
 * IPアドレス
 * The IP address of the current user.
 * @var string
 */
public $ip;

/**
 * SWCから取得したアバター用画像データ
 */
public $userImg;

/**
 * SWCのユーザ情報
 */
public $userInfo;

/**
 * SWC取得情報の戻り値用クラス名
 */
static $swc_rel_classname = "ForumResult";

/**
 * Class constructor: starts the session and initializes class properties (ip, token, user, etc.)
 *
 * @return void
 */
public function __construct()
{
    // Session初期処理
    $sesName = session_name();
    // SWCログインフラグ
    $isLogin = false;
    // SWCユーザ情報
    $userInfo = "";
    if ($_COOKIE[SwcUtils::SWC_SESS_NAME]) {
        // クッキーキーをチェック
        // ログインチェック
        $rtn = SwcUtils::isSwcLogin();
        
        // SWCログイン済の場合
        if ($rtn instanceof self::$swc_rel_classname 
                && $rtn->isLogin) {
            // 戻り値が戻り値クラスインスタンスかつ、ログイン中の場合
            
            $isLogin=true;
            // ユーザ情報取得設定
            $this->userInfo = $rtn->userDetail;
            // セッション設定
            $_SESSION["userId"] = $this->userInfo['user_id'];
        }
    }    
    
    $sesName = session_name();
    if ($isLogin || $sesName == SwcUtils::SWC_SESS_NAME) {
	// ログイン済、またはSWCセッションが開始済の場合
        // セッションはそのまま利用する
        if (empty($_SESSION["token"])) {
            $_SESSION["token"] = substr(md5(uniqid(rand())), 0, 13);
            $_SESSION["userAgent"] = md5($_SERVER["HTTP_USER_AGENT"]);
        }
    } else {
        // セッションがない場合 ETセッション開始
        $etSesName = C("esoTalk.cookie.name")."_session";
        if ($sesName != $etSesName) {
            session_name(C("esoTalk.cookie.name")."_session");
            session_start();
            if (empty($_SESSION["token"])) $this->regenerateToken();
        }
    }
    
    // Complicate session highjacking - check the current user agent against the one that initiated the session.
    if (md5($_SERVER["HTTP_USER_AGENT"]) != $_SESSION["userAgent"]) session_destroy();


    // Set the class properties to reference session variables.
    $this->token = &$_SESSION["token"];
    $this->ip = $_SERVER["REMOTE_ADDR"];
    $this->userId = &$_SESSION["userId"];

    // If there's a user logged in, get their user data.
    // 最新のユーザ情報をセッションに設定する
    if ($this->userId and C("esoTalk.installed")) $this->refreshUserData($this->userInfo);
}


    /**
     * 最新のユーザ情報取得
     * Pulls fresh user data from the database into the $user property.
     * @return void
     *
    // 2016/02 SWCユーザ情報と、DB情報からユーザ情報を取得して、セッションに格納する
     * session 作成時にしか呼ばれないメソッド
     */
    public function refreshUserData($userInfo) {
        // セッションのユーザ情報設定
        $this->user = ET::memberModel()->refreshUserData($userInfo);
    }

/**
 * 外観情報
 * Get the value of a specific preference for the currently logged in user.
 *
 * @return mixed
 */
public function preference($key, $default = false)
{
	return isset($this->user["preferences"][$key]) ? $this->user["preferences"][$key] : $default;
}


/**
 * 外観の設定
 * Set preferences for the current user.
 *
 * @param array $values An array of preferences to set.
 * @return void
 */
public function setPreferences($values)
{
	if (!$this->userId) return;
	$this->user["preferences"] = ET::memberModel()->setPreferences($this->user, $values);
}


/**
 * Set up the session to be logged in with the given member.
 *
 * @param array $member The details of the member to log in with.
 * @return bool true on success, false on error.
 */
protected function processLogin($member)
{
	// If registrations require confirmation but the user's account hasn't been confirmed, return a message.
	if (!$member["confirmed"] and ($type = C("esoTalk.registration.requireConfirmation"))) {
		if ($type == "email") $this->error("emailNotYetConfirmed");
		elseif ($type == "approval") $this->error("accountNotYetApproved");
		return false;
	}

	// Assign the user ID to a SESSION variable.
	$_SESSION["userId"] = $member["memberId"];
	$this->user = $member;

	// Regenerate the session ID and token to prevent session fixation.
	$this->regenerateToken();

	return true;
}


/**
 * Log in the member with the specified ID.
 *
 * @param int $memberId The member ID.
 * @return bool true on success, false on failure.
 */
public function loginWithMemberId($memberId)
{
	$member = ET::memberModel()->getById($memberId);
	return $this->processLogin($member);
}


/**
 * Log in the member with the specified username and password, and optionally set a persistent login cookie.
 *
 * @param string $username The username.
 * @param string $password The password.
 * @param bool $remember Whether or not to set a persistent login cookie.
 * @return bool true on success, false on failure.
 */
public function login($name, $password, $remember = false)
{
	$return = $this->trigger("login", array($name, $password, $remember));
	if (count($return)) return reset($return);

	// Get the member with this username or email.
	$sql = ET::SQL()
		->where("m.username=:username OR m.email=:email")
		->bind(":username", $name)
		->bind(":email", $name);
	$member = reset(ET::memberModel()->getWithSQL($sql));

	// Check that the password is correct.
	if (!$member or !ET::memberModel()->checkPassword($password, $member["password"])) {
		$this->error("password", "incorrectLogin");
		return false;
	}

	// Process the login.
	$return = $this->processLogin($member);

	// Set a persistent login "remember me" cookie?
	if ($return === true and $remember) $this->setRememberCookie($this->userId);

	return $return;
}


/**
 * Create or update a memberId-series-token triplet in the cookie table that can be used to verify a cookie.
 *
 * @param int $memberId The ID of the member that the cookie is being set for.
 * @param string $series The series identifier.
 * @return string $token The token that was generated.
 */
protected function createPersistentToken($memberId, $series)
{
	// Generate a new token.
	$token = md5(generateRandomString(32));

	// Insert or update it in the database.
	ET::SQL()->insert("cookie")->set(array(
		"memberId" => $memberId,
		"series" => $series,
		"token" => $token
	))->setOnDuplicateKey("token", $token)->exec();

	return $token;
}


/**
 * Set a cookie with a standardized name prefix.
 *
 * @param string $name The name of the cookie.
 * @param string $value The value of the cookie.
 * @param int $expire The time before the cookie will expire.
 */
public function setCookie($name, $value, $expire = 0)
{
	return setcookie(C("esoTalk.cookie.name")."_".$name, $value, $expire, C("esoTalk.cookie.path", getWebPath('')), C("esoTalk.cookie.domain"));
}


/**
 * Set a cookie to remember a user.
 *
 * @param int $userId The ID of the user to remember.
 */
public function setRememberCookie($userId)
{
	// We use this implementation: http://jaspan.com/improved_persistent_login_cookie_best_practice

	// Generate a new series identifier, and a token.
	$series = md5(generateRandomString(32));
	$token = $this->createPersistentToken($userId, $series);

	// Set the cookie.
	$this->setCookie("persistent", $userId.$series.$token, time() + C("esoTalk.cookie.expire"));
}


/**
 * Get the value of a cookie set by $this->setCookie().
 *
 * @param string $name The name of the cookie.
 * @param string $default The value to return if the cookie is not set.
 * @return string
 */
public function getCookie($name, $default = null)
{
	$name = C("esoTalk.cookie.name")."_".$name;
	return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
}


/**
 * Log the current user out.
 *
 * @return void
 */
public function logout()
{
	// Destroy session data and regenerate the unique token to prevent session fixation.
	unset($_SESSION["userId"]);
	$this->regenerateToken();

	// Eat the persistent login cookie. OM NOM NOM
	if ($this->getCookie("persistent")) $this->setCookie("persistent", false, -1);

	$this->trigger("logout");
}


/**
 * Update the current session's local user data.
 *
 * @param string $key The key to set.
 * @param mixed $value The value to set.
 * @return void
 */
public function updateUser($key, $value)
{
	$this->user[$key] = $value;
}


/**
 * Check a token against the current valid token.
 *
 * @param string $token The token to check.
 * @return bool Whether or not the token is valid.
 */
public function validateToken($token)
{
	return $token == $this->token;
}


/**
 * Regenerate the session ID, token, and store the user's agent.
 *
 * @return void
 */
public function regenerateToken()
{
	session_regenerate_id(true);
	$_SESSION["token"] = substr(md5(uniqid(rand())), 0, 13);
	$_SESSION["userAgent"] = md5($_SERVER["HTTP_USER_AGENT"]);
}


/**
 * Push an item onto the top of the navigation breadcrumb stack.
 *
 * When adding an item to the navigation breadcrumb stack, we first go through all the items in the stack and
 * check if there's an item with the same ID. If it is found, we go back to that point in the breadcrumb,
 * discarding everything afterwards.
 *
 * @param string $id The navigation ID (a unique ID for this item in the breadcrumb.)
 * @param string $type The type of page this is (search/conversation/etc - will be used in the "back to [type]" text.)
 * @param string $url The URL to this page.
 * @return void
 */
public function pushNavigation($id, $type, $url)
{
	$navigation = $this->get("navigation");
	if (!is_array($navigation)) $navigation = array();

	// Look for an item with this $id that might already by in the navigation. If found, delete everything after it.
	foreach ($navigation as $k => $item) {
		if ($item["id"] == $id) {
			array_splice($navigation, $k);
			break;
		}
	}
	$navigation[] = array("id" => $id, "type" => $type, "url" => $url);

	$this->store("navigation", $navigation);
}


/**
 * Get the item that is on top of the navigation stack. The navigation ID of the current page will be used to
 * make sure the item returned isn't the item for the current page.
 *
 * @param string $currentId The unqiue navigation ID of the current page.
 * @return bool|array The navigation item, or false if there is none (if the current page is the top.)
 */
public function getNavigation($currentId)
{
	$navigation = $this->get("navigation");
	if (!empty($navigation)) {
		$return = end($navigation);
		if ($return["id"] == $currentId) $return = prev($navigation);
		return $return;
	}
	else return false;
}


/**
 * Return whether or not the current user is an administrator.
 *
 * @return bool
 */
public function isAdmin()
{
	return $this->user["account"] == ACCOUNT_ADMINISTRATOR or $this->userId == C("esoTalk.rootAdmin");
}


/**
 * Return whether or not the current user is suspended.
 *
 * @return bool
 */
public function isSuspended()
{
	return $this->user["account"] == ACCOUNT_SUSPENDED;
}


/**
 * Return whether or not the current user is flooding.
 *
 * @return bool
 */
public function isFlooding()
{
	// If there's no wait time between posting configured, they're not flooding.
	if (C("esoTalk.conversation.timeBetweenPosts") <= 0) return false;

	// Otherwise, make sure the time of their most recent conversation/post is more than the time limit ago.
	$time = time() - C("esoTalk.conversation.timeBetweenPosts");
	$recentConversation = (bool)ET::SQL()
		->select("MAX(startTime)>$time")
		->from("conversation")
		->where("startMemberId", $this->userId)
		->exec()
		->result();
	$recentPost = (bool)ET::SQL()
		->select("MAX(time)>$time")
		->from("post p")
		->where("memberId", $this->userId)
		->exec()
		->result();

	return $recentConversation or $recentPost;
}


/**
 * Get a list of group IDs which the current user is in.
 *
 * @return array
 */
public function getGroupIds()
{
	if ($this->user) return ET::groupModel()->getGroupIds($this->user["account"], array_keys($this->user["groups"]));
	else return ET::groupModel()->getGroupIds(false, false);
}


/**
 * Store a value in the session data store.
 *
 * @return void
 */
public function store($key, $value)
{
	$_SESSION[$key] = $value;
}


/**
 * Retrieve a value from the session data store.
 *
 * @return mixed
 */
public function get($key, $default = null)
{
	return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}


/**
 * Remove a value from the session data store.
 *
 * @return void
 */
public function remove($key)
{
	unset($_SESSION[$key]);
}

}
