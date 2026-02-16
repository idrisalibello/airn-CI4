<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRoleModel extends Model
{
    protected $table = 'user_roles';
    protected $returnType = 'array';

    private static ?string $roleKeyColumn = null;

    private function detectRoleKeyColumn(): string
    {
        if (self::$roleKeyColumn !== null) {
            return self::$roleKeyColumn;
        }

        $db = \Config\Database::connect();
        $cols = $db->getFieldNames('roles'); // returns column names

        if (in_array('role_key', $cols, true)) {
            self::$roleKeyColumn = 'role_key';
        } elseif (in_array('key', $cols, true)) {
            self::$roleKeyColumn = 'key';
        } else {
            // fallback: force something obvious so you see the error
            self::$roleKeyColumn = 'role_key';
        }

        return self::$roleKeyColumn;
    }

    public function getRoleKeysForUser(int $userId): array
    {
        $db = \Config\Database::connect();
        $col = $this->detectRoleKeyColumn();

        // backtick in case column is `key`
        $select = "r.`{$col}` AS role_key";

        $rows = $db->table('user_roles ur')
            ->select($select)
            ->join('roles r', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->getResultArray();

        return array_map(static fn($r) => $r['role_key'], $rows);
    }
}
