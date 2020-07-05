<?php

class UserBalance_model extends CI_Emerald_Model
{
    const CLASS_TABLE = 'user_balance';

    /** @var int */
    protected $user_id;
    /** @var float */
    protected $amount;
    /** @var string */
    protected $time_created;

    public function get_amount(): float
    {
        return $this->amount;
    }

    /**
     * @param int $user_id
     * @param float $amount
     * @return static
     * @throws Exception
     */
    public static function create(int $user_id, float $amount)
    {
        $latest_transaction = self::get_latest_transaction($user_id);
        $latest_check_amount = $latest_transaction ? $latest_transaction->get_amount() : 0;
        $check_amount = $latest_check_amount + $amount;

        App::get_ci()->s->from(self::CLASS_TABLE)->insert(
            compact('user_id', 'amount', 'check_amount')
        )->execute();
        return new static(App::get_ci()->s->get_insert_id());
    }

    /**
     * @param int $user_id
     * @return UserBalance_model|null
     */
    protected static function get_latest_transaction(int $user_id): ?self
    {
        $data = App::get_ci()->s->from(self::CLASS_TABLE)
            ->where('user_id', $user_id)
            ->orderBy('time_created', 'desc')
            ->limit(1)
            ->one();

        if (!$data) {
            return null;
        }

        return (new self)->set($data);
    }
}