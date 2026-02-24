<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class ConferencesController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $items = $db->table('conferences')->orderBy('start_date', 'DESC')->get()->getResultArray();

        return view('admin/conferences/index', [
            'title' => 'Conferences (Admin)',
            'items' => $items,
            'flash' => session('flash'),
        ]);
    }

    public function new()
    {
        return view('admin/conferences/form', [
            'title' => 'New Conference',
            'mode' => 'create',
            'item' => [
                'name' => '',
                'slug' => '',
                'start_date' => '',
                'end_date' => '',
                'venue' => '',
                'theme' => '',
                'announcement' => '',
            ],
            'error' => session('error'),
        ]);
    }

    public function create()
    {
        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $start = trim((string)$this->request->getPost('start_date'));
        $end = trim((string)$this->request->getPost('end_date'));
        $venue = trim((string)$this->request->getPost('venue'));
        $theme = trim((string)$this->request->getPost('theme'));
        $announcement = trim((string)$this->request->getPost('announcement'));

        if ($name === '' || $slug === '') {
            return redirect()->to('/admin/conferences/new')->with('error', 'Name and slug are required.');
        }

        $db = \Config\Database::connect();

        $exists = $db->table('conferences')->where('slug', $slug)->get()->getRowArray();
        if ($exists) {
            return redirect()->to('/admin/conferences/new')->with('error', 'Slug already exists.');
        }

        $db->table('conferences')->insert([
            'name' => $name,
            'slug' => $slug,
            'start_date' => ($start !== '' ? $start : null),
            'end_date' => ($end !== '' ? $end : null),
            'venue' => ($venue !== '' ? $venue : null),
            'settings_json' => (($theme!=='' || $announcement!=='') ? json_encode(['theme'=>$theme,'announcement'=>$announcement], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/conferences')->with('flash', 'Conference created.');
    }

    public function edit(int $id)
    {
        $db = \Config\Database::connect();
        $item = $db->table('conferences')->where('id', $id)->get()->getRowArray();
        if (!$item) throw PageNotFoundException::forPageNotFound('Conference not found');

        $settings = [];
        if (!empty($item['settings_json'])) {
            $tmp = json_decode((string)$item['settings_json'], true);
            if (is_array($tmp)) $settings = $tmp;
        }
        $item['theme'] = (string)($settings['theme'] ?? '');
        $item['announcement'] = (string)($settings['announcement'] ?? '');

        return view('admin/conferences/form', [
            'title' => 'Edit Conference',
            'mode' => 'update',
            'item' => $item,
            'error' => session('error'),
        ]);
    }

    public function update(int $id)
    {
        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $start = trim((string)$this->request->getPost('start_date'));
        $end = trim((string)$this->request->getPost('end_date'));
        $venue = trim((string)$this->request->getPost('venue'));
        $theme = trim((string)$this->request->getPost('theme'));
        $announcement = trim((string)$this->request->getPost('announcement'));

        if ($name === '' || $slug === '') {
            return redirect()->to("/admin/conferences/{$id}/edit")->with('error', 'Name and slug are required.');
        }

        $db = \Config\Database::connect();

        $item = $db->table('conferences')->where('id', $id)->get()->getRowArray();
        if (!$item) throw PageNotFoundException::forPageNotFound('Conference not found');

        $exists = $db->table('conferences')
            ->where('slug', $slug)
            ->where('id !=', $id)
            ->get()
            ->getRowArray();

        if ($exists) {
            return redirect()->to("/admin/conferences/{$id}/edit")->with('error', 'Slug already exists.');
        }

        $db->table('conferences')->where('id', $id)->update([
            'name' => $name,
            'slug' => $slug,
            'start_date' => ($start !== '' ? $start : null),
            'end_date' => ($end !== '' ? $end : null),
            'venue' => ($venue !== '' ? $venue : null),
            'settings_json' => (($theme!=='' || $announcement!=='') ? json_encode(['theme'=>$theme,'announcement'=>$announcement], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/conferences')->with('flash', 'Conference updated.');
    }

    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $db->table('conferences')->where('id', $id)->delete();

        return redirect()->to('/admin/conferences')->with('flash', 'Conference deleted.');
    }
}
