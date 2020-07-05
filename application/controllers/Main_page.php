<?php

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        App::get_ci()->load->model('User_model');
        App::get_ci()->load->model('Login_model');
        App::get_ci()->load->model('Post_model');
        App::get_ci()->load->library('form_validation');

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();



        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation(Post_model::get_all(), 'main_page');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_post($post_id){ // or can be $this->input->post('news_id') , but better for GET REQUEST USE THIS

        $post_id = intval($post_id);

        if (empty($post_id)){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try
        {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }


        $posts =  Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }

    /**
     * @param $post_id
     * @param null $comment_id - is sent if the comment is a reply to another comment
     * @return object|string|void
     * @throws Exception
     */
    public function comment($post_id, $comment_id = null)
    { // or can be App::get_ci()->input->post('news_id') , but better for GET REQUEST USE THIS ( tests )
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $post_id = intval($post_id);

        if (empty($post_id)) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $post = new Post_model($post_id);
        } catch (EmeraldModelNoDataException $ex){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
        }

        if ($comment_id) {
            try {
                $comment = new Comment_model($comment_id);
            } catch (EmeraldModelNoDataException $ex){
                return $this->response_error(CI_Core::RESPONSE_GENERIC_NO_DATA);
            }

            if ($comment->get_assign_id() !== $post_id) {
                //Ideally this should never occur - check if the comment we are trying to reply to is from the correct post
                return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
            }
        }

        //TODO: we can validate comment length here, moderation can also be performed here
        $this->form_validation->set_rules('text', 'Text', 'required');
        if (!$this->form_validation->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        Comment_model::create([
            'user_id' => User_model::get_session_id(),
            'assign_id' => $post_id,
            'parent_id' => $comment_id,
            'text' => $this->input->post('text'),
        ]);

        $posts = Post_model::preparation($post, 'full_info');
        return $this->response_success(['post' => $posts]);
    }


    public function login()
    {
        //validate input first
        $this->form_validation->set_rules('login', 'Login', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if (!$this->form_validation->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        try {
            $user = Login_model::attempt(
                $this->input->post('login'),
                $this->input->post('password')
            );
        } catch (CriticalException $e) {
            //TODO: pass more verbose message to frontend
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        //start session only if the attempt was successful
        Login_model::start_session($user->get_id());

        return $this->response_success(['user' => $user->get_id()]);
    }


    public function logout()
    {
        Login_model::logout();
        redirect(site_url('/'));
    }

    public function add_money()
    {
        if (!User_model::is_logged()){
            return $this->response_error(CI_Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        $this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]|less_than_equal_to[1000]');
        if (!$this->form_validation->run()) {
            return $this->response_error(CI_Core::RESPONSE_GENERIC_WRONG_PARAMS);
        }

        $user = User_model::get_user();
        $user->add_money(
            $this->input->post('amount')
        );

        return $this->response_success(['amount' => rand(1,55)]);
    }

    public function buy_boosterpack(){
        // todo: add money to user logic
        return $this->response_success(['amount' => rand(1,55)]);
    }


    public function like(){
        // todo: add like post\comment logic
        return $this->response_success(['likes' => rand(1,55)]); // Колво лайков под постом \ комментарием чтобы обновить
    }

}
