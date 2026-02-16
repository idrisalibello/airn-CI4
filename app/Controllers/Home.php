<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return 'start coding';
    }

    public function dbtest(){
        return 'hello';
    //      try {
    //     $db = \Config\Database::connect();
    //     $row = $db->query("SELECT DATABASE() AS db, USER() AS u")->getRowArray();
    //     return "DB OK. db=" . ($row['db'] ?? '-') . " user=" . ($row['u'] ?? '-');
    // } catch (\Throwable $e) {
    //     return "DB FAIL: " . $e->getMessage();
    // }
    }
}
