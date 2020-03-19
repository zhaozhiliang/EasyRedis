<?php
namespace EasyRedis\chat\group;


class Group
{
    public function create(\Redis $redis, $sender, $recipients, $message, $groupId)
    {

    }

    public function sendMessage(\Redis $redis, $groupId, $sender, $message)
    {

    }

    public function join(\Redis $redis, $groupId, $userId)
    {

    }

    public function leave(\Redis $redis, $groupId, $userId)
    {

    }
}