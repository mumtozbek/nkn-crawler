<?php

namespace App\Http\Controllers;

use App\DataTables\NodesDataTable;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param NodesDataTable $dataTable
     * @return \Illuminate\Http\Response
     */
    public function index(NodesDataTable $dataTable)
    {
        return $dataTable->render('nodes.index');
    }
}
