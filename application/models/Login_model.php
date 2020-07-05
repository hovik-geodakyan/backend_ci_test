<?php
class Login_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $login
     * @param string $password
     * @return User_model
     * @throws CriticalException
     */
    public static function attempt(string $login, string $password)
    {
        //safe to assume it's either the user we want or null because email is unique
        $user = User_model::get_by_credentials($login);

        if (!$user) {
            throw new CriticalException('User not found');
        }

        //password not encrypted for easier testing
        if ($user->get_password() !== $password) {
            //TODO: log login attempt for possible throttle functionality
            throw new CriticalException('Invalid Password');
        }

        return $user;
    }

    public static function logout()
    {
        App::get_ci()->session->unset_userdata('id');
    }

    public static function start_session(int $user_id)
    {
        // если перенедан пользователь
        if (empty($user_id))
        {
            throw new CriticalException('No id provided!');
        }

        App::get_ci()->session->set_userdata('id', $user_id);
    }


}
