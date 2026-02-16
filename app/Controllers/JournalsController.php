<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

class JournalsController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $items = $db->table('journals')->orderBy('id', 'DESC')->get()->getResultArray();

        return view('admin/journals/index', [
            'title' => 'Journals (Admin)',
            'items' => $items,
            'flash' => session('flash'),
        ]);
    }

    public function new()
    {
        return view('admin/journals/form', [
            'title' => 'New Journal',
            'mode' => 'create',
            'item' => ['name' => '', 'slug' => '', 'issn' => '', 'description' => ''],
            'error' => session('error'),
        ]);
    }

    public function create()
    {
        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $issn = trim((string)$this->request->getPost('issn'));
        $description = trim((string)$this->request->getPost('description'));

        if ($name === '' || $slug === '') {
            return redirect()->to('/admin/journals/new')->with('error', 'Name and slug are required.');
        }

        $db = \Config\Database::connect();

        $exists = $db->table('journals')->where('slug', $slug)->get()->getRowArray();
        if ($exists) {
            return redirect()->to('/admin/journals/new')->with('error', 'Slug already exists.');
        }

        $db->table('journals')->insert([
            'name' => $name,
            'slug' => $slug,
            'issn' => ($issn !== '' ? $issn : null),
            'description' => ($description !== '' ? $description : null),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/journals')->with('flash', 'Journal created.');
    }

    public function edit(int $id)
    {
        $db = \Config\Database::connect();
        $item = $db->table('journals')->where('id', $id)->get()->getRowArray();
        if (!$item) throw PageNotFoundException::forPageNotFound('Journal not found');

        return view('admin/journals/form', [
            'title' => 'Edit Journal',
            'mode' => 'update',
            'item' => $item,
            'error' => session('error'),
        ]);
    }

    public function update(int $id)
    {
        $name = trim((string)$this->request->getPost('name'));
        $slug = trim((string)$this->request->getPost('slug'));
        $issn = trim((string)$this->request->getPost('issn'));
        $description = trim((string)$this->request->getPost('description'));

        if ($name === '' || $slug === '') {
            return redirect()->to("/admin/journals/{$id}/edit")->with('error', 'Name and slug are required.');
        }

        $db = \Config\Database::connect();

        $item = $db->table('journals')->where('id', $id)->get()->getRowArray();
        if (!$item) throw PageNotFoundException::forPageNotFound('Journal not found');

        $exists = $db->table('journals')
            ->where('slug', $slug)
            ->where('id !=', $id)
            ->get()
            ->getRowArray();

        if ($exists) {
            return redirect()->to("/admin/journals/{$id}/edit")->with('error', 'Slug already exists.');
        }

        $db->table('journals')->where('id', $id)->update([
            'name' => $name,
            'slug' => $slug,
            'issn' => ($issn !== '' ? $issn : null),
            'description' => ($description !== '' ? $description : null),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/journals')->with('flash', 'Journal updated.');
    }

    public function delete(int $id)
    {
        $db = \Config\Database::connect();
        $db->table('journals')->where('id', $id)->delete();

        return redirect()->to('/admin/journals')->with('flash', 'Journal deleted.');
    }
}
