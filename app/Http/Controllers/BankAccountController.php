<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        return view('backoffice.bank-account');
    }

    public function store()
    {
        return view('backoffice.bank-account.create');
    }

    public function update($id)
    {
        return view('backoffice.bank-account.edit');
    }

    public function delete($id)
    {
        return view('backoffice.bank-account.delete');
    }
}
