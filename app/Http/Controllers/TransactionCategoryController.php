<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionCategoryController extends Controller
{
    public function index()
    {
        return view('backoffice.transactions.categories.index');
    }
    public function create()
    {
        return view('backoffice.transactions.categories.create');
    }

    public function edit($id)
    {
        return view('backoffice.transactions.categories.edit');
    }
}
