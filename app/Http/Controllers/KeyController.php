<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UpdateKeyRequest;
use App\Models\Key;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class KeyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    static function getPubkeys()
    {
        $data_raw = '{
            "collection_result": {
                "total_elements_count": 867,
                "filtered_elements_count": 867
            },
            "mac_table_entry_element": [
                {
                    "uri": "/mac-table/000105-41f749",
                    "mac_address": "000105-41f749",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-cb9c04",
                    "mac_address": "08000f-cb9c04",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b65802-340bde",
                    "mac_address": "b65802-340bde",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/00206b-4154e2",
                    "mac_address": "00206b-4154e2",
                    "port_id": "Trk8",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/38b19e-9000ff",
                    "mac_address": "38b19e-9000ff",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/788c77-084cdb",
                    "mac_address": "788c77-084cdb",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/00206b-e28bfa",
                    "mac_address": "00206b-e28bfa",
                    "port_id": "Trk7",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/00ce39-cc5934",
                    "mac_address": "00ce39-cc5934",
                    "port_id": "A11",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/08000f-cb864d",
                    "mac_address": "08000f-cb864d",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/d2f9f9-03ecce",
                    "mac_address": "d2f9f9-03ecce",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/00206b-0007df",
                    "mac_address": "00206b-0007df",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/002406-f41db2",
                    "mac_address": "002406-f41db2",
                    "port_id": "Trk2",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/00206b-4e1780",
                    "mac_address": "00206b-4e1780",
                    "port_id": "B18",
                    "vlan_id": 515
                },
                {
                    "uri": "/mac-table/08000f-cb986f",
                    "mac_address": "08000f-cb986f",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/000105-41f751",
                    "mac_address": "000105-41f751",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/7c5a1c-cb60f4",
                    "mac_address": "7c5a1c-cb60f4",
                    "port_id": "C14",
                    "vlan_id": 3904
                },
                {
                    "uri": "/mac-table/4acf93-d9f1e9",
                    "mac_address": "4acf93-d9f1e9",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/68d79a-dcb1fe",
                    "mac_address": "68d79a-dcb1fe",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-bfbfa7",
                    "mac_address": "005056-bfbfa7",
                    "port_id": "B21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/9020c2-4af2c0",
                    "mac_address": "9020c2-4af2c0",
                    "port_id": "Trk4",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 30
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/7483c2-cc41c6",
                    "mac_address": "7483c2-cc41c6",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/fc3fdb-0a5f93",
                    "mac_address": "fc3fdb-0a5f93",
                    "port_id": "D9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-9000a2",
                    "mac_address": "38b19e-9000a2",
                    "port_id": "Trk144",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/3460f9-6e04ca",
                    "mac_address": "3460f9-6e04ca",
                    "port_id": "A9",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/0e2a99-385b0b",
                    "mac_address": "0e2a99-385b0b",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 562
                },
                {
                    "uri": "/mac-table/64cba3-f17383",
                    "mac_address": "64cba3-f17383",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2006
                },
                {
                    "uri": "/mac-table/c2b55e-3bf1cc",
                    "mac_address": "c2b55e-3bf1cc",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/d8cb8a-54539b",
                    "mac_address": "d8cb8a-54539b",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/d47226-003e0a",
                    "mac_address": "d47226-003e0a",
                    "port_id": "Trk6",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-ce07e0",
                    "mac_address": "08000f-ce07e0",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/001dc1-503c75",
                    "mac_address": "001dc1-503c75",
                    "port_id": "Trk8",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/806d97-1b7fa6",
                    "mac_address": "806d97-1b7fa6",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-bff33b",
                    "mac_address": "005056-bff33b",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1710
                },
                {
                    "uri": "/mac-table/001a8c-f0ca44",
                    "mac_address": "001a8c-f0ca44",
                    "port_id": "B19",
                    "vlan_id": 2003
                },
                {
                    "uri": "/mac-table/7483c2-cc41c0",
                    "mac_address": "7483c2-cc41c0",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/74fe48-1f2f9b",
                    "mac_address": "74fe48-1f2f9b",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/6c4b90-d0ef35",
                    "mac_address": "6c4b90-d0ef35",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-90003b",
                    "mac_address": "38b19e-90003b",
                    "port_id": "Trk10",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/56f9ab-cd7cf1",
                    "mac_address": "56f9ab-cd7cf1",
                    "port_id": "Trk4",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/002406-f38515",
                    "mac_address": "002406-f38515",
                    "port_id": "Trk1",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/38b19e-9000bb",
                    "mac_address": "38b19e-9000bb",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-cb9b7d",
                    "mac_address": "08000f-cb9b7d",
                    "port_id": "Trk1",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/90fba6-84d83b",
                    "mac_address": "90fba6-84d83b",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/602232-2c64a5",
                    "mac_address": "602232-2c64a5",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 515
                },
                {
                    "uri": "/mac-table/005056-bf042b",
                    "mac_address": "005056-bf042b",
                    "port_id": "B21",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/b05cda-01202a",
                    "mac_address": "b05cda-01202a",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/448a5b-756cad",
                    "mac_address": "448a5b-756cad",
                    "port_id": "Trk6",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/bc305b-f44973",
                    "mac_address": "bc305b-f44973",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/90b11c-5274df",
                    "mac_address": "90b11c-5274df",
                    "port_id": "A5",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 99
                },
                {
                    "uri": "/mac-table/08000f-ce161f",
                    "mac_address": "08000f-ce161f",
                    "port_id": "Trk1",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/74fe48-3bf30c",
                    "mac_address": "74fe48-3bf30c",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/68d79a-dc8f14",
                    "mac_address": "68d79a-dc8f14",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/7483c2-cc425f",
                    "mac_address": "7483c2-cc425f",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-f4b447",
                    "mac_address": "08000f-f4b447",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b00cd1-a3ecba",
                    "mac_address": "b00cd1-a3ecba",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-bf14ab",
                    "mac_address": "005056-bf14ab",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00206b-009d6a",
                    "mac_address": "00206b-009d6a",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/005056-9a93e2",
                    "mac_address": "005056-9a93e2",
                    "port_id": "B21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/9e878e-de490c",
                    "mac_address": "9e878e-de490c",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/001dc1-7147c9",
                    "mac_address": "001dc1-7147c9",
                    "port_id": "Trk9",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/005056-bf26a3",
                    "mac_address": "005056-bf26a3",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/c83ea7-01ae84",
                    "mac_address": "c83ea7-01ae84",
                    "port_id": "A12",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/f492bf-c38605",
                    "mac_address": "f492bf-c38605",
                    "port_id": "Trk144",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/806d97-1e0acd",
                    "mac_address": "806d97-1e0acd",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/005056-bf641f",
                    "mac_address": "005056-bf641f",
                    "port_id": "Trk144",
                    "vlan_id": 2006
                },
                {
                    "uri": "/mac-table/38b19e-900062",
                    "mac_address": "38b19e-900062",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/089204-bd54fe",
                    "mac_address": "089204-bd54fe",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/08000f-cb9a2b",
                    "mac_address": "08000f-cb9a2b",
                    "port_id": "F10",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 503
                },
                {
                    "uri": "/mac-table/ced750-0bec6f",
                    "mac_address": "ced750-0bec6f",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/cc7314-777d87",
                    "mac_address": "cc7314-777d87",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-bfdf89",
                    "mac_address": "005056-bfdf89",
                    "port_id": "Trk144",
                    "vlan_id": 2025
                },
                {
                    "uri": "/mac-table/08000f-cb9821",
                    "mac_address": "08000f-cb9821",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2006
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2008
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2009
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2022
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2025
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2031
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2033
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2034
                },
                {
                    "uri": "/mac-table/08000f-ce1575",
                    "mac_address": "08000f-ce1575",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2080
                },
                {
                    "uri": "/mac-table/be7fb5-894365",
                    "mac_address": "be7fb5-894365",
                    "port_id": "C4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/dca632-248211",
                    "mac_address": "dca632-248211",
                    "port_id": "F16",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d45788",
                    "mac_address": "1458d0-d45788",
                    "port_id": "F24",
                    "vlan_id": 999
                },
                {
                    "uri": "/mac-table/005056-81e5ce",
                    "mac_address": "005056-81e5ce",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2099
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3003
                },
                {
                    "uri": "/mac-table/3821c7-afc680",
                    "mac_address": "3821c7-afc680",
                    "port_id": "Trk144",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3902
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3903
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3904
                },
                {
                    "uri": "/mac-table/a4ae11-0fbc23",
                    "mac_address": "a4ae11-0fbc23",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/548d5a-ebb220",
                    "mac_address": "548d5a-ebb220",
                    "port_id": "C4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/002406-f38435",
                    "mac_address": "002406-f38435",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2032
                },
                {
                    "uri": "/mac-table/38b19e-9001f9",
                    "mac_address": "38b19e-9001f9",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/0e1b8d-275f1a",
                    "mac_address": "0e1b8d-275f1a",
                    "port_id": "Trk9",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/38b19e-90004d",
                    "mac_address": "38b19e-90004d",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/005056-bfff29",
                    "mac_address": "005056-bfff29",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/4437e6-ddb288",
                    "mac_address": "4437e6-ddb288",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/7483c2-cc41c6",
                    "mac_address": "7483c2-cc41c6",
                    "port_id": "Trk8",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/005056-bf2dd3",
                    "mac_address": "005056-bf2dd3",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/005056-bf6bff",
                    "mac_address": "005056-bf6bff",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/b4fbe4-c3649c",
                    "mac_address": "b4fbe4-c3649c",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/94de80-a9b90b",
                    "mac_address": "94de80-a9b90b",
                    "port_id": "Trk1",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/38b19e-9001fe",
                    "mac_address": "38b19e-9001fe",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/005056-bfef69",
                    "mac_address": "005056-bfef69",
                    "port_id": "A21",
                    "vlan_id": 3003
                },
                {
                    "uri": "/mac-table/18e829-e01010",
                    "mac_address": "18e829-e01010",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3053
                },
                {
                    "uri": "/mac-table/7085c2-8a575e",
                    "mac_address": "7085c2-8a575e",
                    "port_id": "Trk8",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/74fe48-3df138",
                    "mac_address": "74fe48-3df138",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/38b19e-9000fd",
                    "mac_address": "38b19e-9000fd",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/001e4f-204942",
                    "mac_address": "001e4f-204942",
                    "port_id": "C5",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/08000f-ce160a",
                    "mac_address": "08000f-ce160a",
                    "port_id": "Trk7",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/6c3be5-2d2ad4",
                    "mac_address": "6c3be5-2d2ad4",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/74fe48-3d2498",
                    "mac_address": "74fe48-3d2498",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/38b19e-9000b3",
                    "mac_address": "38b19e-9000b3",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/00206b-4cd932",
                    "mac_address": "00206b-4cd932",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/8030e0-84a800",
                    "mac_address": "8030e0-84a800",
                    "port_id": "Trk9",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2003
                },
                {
                    "uri": "/mac-table/38b19e-900059",
                    "mac_address": "38b19e-900059",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/00be43-f9ec09",
                    "mac_address": "00be43-f9ec09",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/18e829-e01010",
                    "mac_address": "18e829-e01010",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 10
                },
                {
                    "uri": "/mac-table/00206b-e2aeec",
                    "mac_address": "00206b-e2aeec",
                    "port_id": "Trk8",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1400e9-020531",
                    "mac_address": "1400e9-020531",
                    "port_id": "Trk6",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 590
                },
                {
                    "uri": "/mac-table/4ec716-f3f258",
                    "mac_address": "4ec716-f3f258",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-9000be",
                    "mac_address": "38b19e-9000be",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 254
                },
                {
                    "uri": "/mac-table/ec8c9a-9c8cf6",
                    "mac_address": "ec8c9a-9c8cf6",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-ec02ea",
                    "mac_address": "08000f-ec02ea",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b42e99-2c3897",
                    "mac_address": "b42e99-2c3897",
                    "port_id": "Trk9",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/005056-bf5ec5",
                    "mac_address": "005056-bf5ec5",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/f4939f-f3820b",
                    "mac_address": "f4939f-f3820b",
                    "port_id": "E14",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/288088-62b6cc",
                    "mac_address": "288088-62b6cc",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/00206b-e2b08a",
                    "mac_address": "00206b-e2b08a",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/6c4b90-5c9100",
                    "mac_address": "6c4b90-5c9100",
                    "port_id": "E12",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/38b19e-9000a5",
                    "mac_address": "38b19e-9000a5",
                    "port_id": "Trk10",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-cb8c70",
                    "mac_address": "08000f-cb8c70",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 592
                },
                {
                    "uri": "/mac-table/38b19e-9000c8",
                    "mac_address": "38b19e-9000c8",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/00085d-93b0ab",
                    "mac_address": "00085d-93b0ab",
                    "port_id": "F20",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/36e92b-4258ce",
                    "mac_address": "36e92b-4258ce",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/f4939f-f36ca3",
                    "mac_address": "f4939f-f36ca3",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-bfa57d",
                    "mac_address": "005056-bfa57d",
                    "port_id": "B21",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/005056-bfa0e6",
                    "mac_address": "005056-bfa0e6",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/6cb311-5e45ef",
                    "mac_address": "6cb311-5e45ef",
                    "port_id": "H22",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/005056-bf3b01",
                    "mac_address": "005056-bf3b01",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00ce39-cc59e0",
                    "mac_address": "00ce39-cc59e0",
                    "port_id": "Trk144",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/18e829-da410c",
                    "mac_address": "18e829-da410c",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/38b19e-9001c4",
                    "mac_address": "38b19e-9001c4",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/f2e216-0ebfc5",
                    "mac_address": "f2e216-0ebfc5",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/002406-f55b34",
                    "mac_address": "002406-f55b34",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/b827eb-119653",
                    "mac_address": "b827eb-119653",
                    "port_id": "Trk4",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1070
                },
                {
                    "uri": "/mac-table/005056-bf7b18",
                    "mac_address": "005056-bf7b18",
                    "port_id": "A21",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/b4fbe4-c3633f",
                    "mac_address": "b4fbe4-c3633f",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/1400e9-0a7659",
                    "mac_address": "1400e9-0a7659",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/7c5a1c-cb60fa",
                    "mac_address": "7c5a1c-cb60fa",
                    "port_id": "Trk30",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/08000f-cb9c32",
                    "mac_address": "08000f-cb9c32",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bfdb43",
                    "mac_address": "005056-bfdb43",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/ec2a72-32c53a",
                    "mac_address": "ec2a72-32c53a",
                    "port_id": "A2",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/001a8c-f0ca45",
                    "mac_address": "001a8c-f0ca45",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 591
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 91
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 592
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 515
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/38b19e-9000ca",
                    "mac_address": "38b19e-9000ca",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/30074d-a03f54",
                    "mac_address": "30074d-a03f54",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/3e3359-8c25b1",
                    "mac_address": "3e3359-8c25b1",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-9ae290",
                    "mac_address": "005056-9ae290",
                    "port_id": "A21",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 92
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 515
                },
                {
                    "uri": "/mac-table/18e829-e00ef7",
                    "mac_address": "18e829-e00ef7",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/448a5b-757f00",
                    "mac_address": "448a5b-757f00",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/00196f-101370",
                    "mac_address": "00196f-101370",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/005056-bf17e0",
                    "mac_address": "005056-bf17e0",
                    "port_id": "B21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/d89d67-f4a840",
                    "mac_address": "d89d67-f4a840",
                    "port_id": "Trk4",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/00206b-000738",
                    "mac_address": "00206b-000738",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/38b19e-9000cc",
                    "mac_address": "38b19e-9000cc",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 503
                },
                {
                    "uri": "/mac-table/08000f-ce1602",
                    "mac_address": "08000f-ce1602",
                    "port_id": "E19",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/3810f0-22c900",
                    "mac_address": "3810f0-22c900",
                    "port_id": "Trk9",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/000105-2d6fad",
                    "mac_address": "000105-2d6fad",
                    "port_id": "Trk1",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 99
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 103
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 254
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1010
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1720
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1730
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2003
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2009
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2022
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2025
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2031
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2032
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2033
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2034
                },
                {
                    "uri": "/mac-table/8cd9d6-22546e",
                    "mac_address": "8cd9d6-22546e",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/001a8c-f0ca44",
                    "mac_address": "001a8c-f0ca44",
                    "port_id": "B19",
                    "vlan_id": 2006
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2080
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2099
                },
                {
                    "uri": "/mac-table/08000f-cb9c00",
                    "mac_address": "08000f-cb9c00",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3003
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3014
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3051
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3101
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3901
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3902
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3903
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3904
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3052
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3053
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2020
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/000123-340cb3",
                    "mac_address": "000123-340cb3",
                    "port_id": "Trk144",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/18e829-e00ef7",
                    "mac_address": "18e829-e00ef7",
                    "port_id": "C4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/f60c31-efe731",
                    "mac_address": "f60c31-efe731",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-bf5cbb",
                    "mac_address": "005056-bf5cbb",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb8c47",
                    "mac_address": "08000f-cb8c47",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-ce1604",
                    "mac_address": "08000f-ce1604",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-f47f59",
                    "mac_address": "08000f-f47f59",
                    "port_id": "B12",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1710
                },
                {
                    "uri": "/mac-table/6cc217-a6b780",
                    "mac_address": "6cc217-a6b780",
                    "port_id": "Trk4",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/6cc217-a63700",
                    "mac_address": "6cc217-a63700",
                    "port_id": "Trk3",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/00206b-a28a1e",
                    "mac_address": "00206b-a28a1e",
                    "port_id": "B17",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/00ce39-cc37b5",
                    "mac_address": "00ce39-cc37b5",
                    "port_id": "F13",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/08000f-cb972e",
                    "mac_address": "08000f-cb972e",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2020
                },
                {
                    "uri": "/mac-table/000105-15e560",
                    "mac_address": "000105-15e560",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-cb9ce3",
                    "mac_address": "08000f-cb9ce3",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/3024a9-78ddfd",
                    "mac_address": "3024a9-78ddfd",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/1e4140-0c025e",
                    "mac_address": "1e4140-0c025e",
                    "port_id": "Trk8",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/ec2a72-32c156",
                    "mac_address": "ec2a72-32c156",
                    "port_id": "Trk144",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/68d79a-dcbb71",
                    "mac_address": "68d79a-dcbb71",
                    "port_id": "Trk3",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-9000fb",
                    "mac_address": "38b19e-9000fb",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/b827eb-455e0e",
                    "mac_address": "b827eb-455e0e",
                    "port_id": "Trk4",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/003064-2a5860",
                    "mac_address": "003064-2a5860",
                    "port_id": "Trk3",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/38b19e-90013d",
                    "mac_address": "38b19e-90013d",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-cb9730",
                    "mac_address": "08000f-cb9730",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/74fe48-3deee0",
                    "mac_address": "74fe48-3deee0",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/08000f-cb9874",
                    "mac_address": "08000f-cb9874",
                    "port_id": "Trk10",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/f2b0d0-e7936c",
                    "mac_address": "f2b0d0-e7936c",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/404e36-b54f68",
                    "mac_address": "404e36-b54f68",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/9cda3e-d6e1ba",
                    "mac_address": "9cda3e-d6e1ba",
                    "port_id": "Trk3",
                    "vlan_id": 3053
                },
                {
                    "uri": "/mac-table/08000f-cb99c0",
                    "mac_address": "08000f-cb99c0",
                    "port_id": "Trk6",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-ec04a3",
                    "mac_address": "08000f-ec04a3",
                    "port_id": "E6",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/00016c-4488be",
                    "mac_address": "00016c-4488be",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/08000f-dcea51",
                    "mac_address": "08000f-dcea51",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/38b19e-90005a",
                    "mac_address": "38b19e-90005a",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/667429-1d5e63",
                    "mac_address": "667429-1d5e63",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-b43333",
                    "mac_address": "005056-b43333",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/000105-41f741",
                    "mac_address": "000105-41f741",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/38b19e-900101",
                    "mac_address": "38b19e-900101",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/7483c2-cc425f",
                    "mac_address": "7483c2-cc425f",
                    "port_id": "Trk8",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/005056-bf3624",
                    "mac_address": "005056-bf3624",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/38b19e-90005f",
                    "mac_address": "38b19e-90005f",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/dcfb02-be13b8",
                    "mac_address": "dcfb02-be13b8",
                    "port_id": "E10",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/b4fbe4-c350ec",
                    "mac_address": "b4fbe4-c350ec",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/08000f-cb9866",
                    "mac_address": "08000f-cb9866",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/00074d-93eb33",
                    "mac_address": "00074d-93eb33",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/000054-fcd4c0",
                    "mac_address": "000054-fcd4c0",
                    "port_id": "Trk144",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/b4fbe4-c3633f",
                    "mac_address": "b4fbe4-c3633f",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1070
                },
                {
                    "uri": "/mac-table/68d79a-dcb030",
                    "mac_address": "68d79a-dcb030",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/001a8c-f0ca45",
                    "mac_address": "001a8c-f0ca45",
                    "port_id": "B15",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/005056-bfad76",
                    "mac_address": "005056-bfad76",
                    "port_id": "B21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/18e829-da4161",
                    "mac_address": "18e829-da4161",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/1c697a-25923e",
                    "mac_address": "1c697a-25923e",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/005056-9aef94",
                    "mac_address": "005056-9aef94",
                    "port_id": "B21",
                    "vlan_id": 81
                },
                {
                    "uri": "/mac-table/68d79a-dc8c65",
                    "mac_address": "68d79a-dc8c65",
                    "port_id": "Trk4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/c84f86-0575b7",
                    "mac_address": "c84f86-0575b7",
                    "port_id": "A10",
                    "vlan_id": 3901
                },
                {
                    "uri": "/mac-table/008017-ab8e79",
                    "mac_address": "008017-ab8e79",
                    "port_id": "Trk9",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/b4fbe4-c35779",
                    "mac_address": "b4fbe4-c35779",
                    "port_id": "Trk4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/fc3fdb-29153e",
                    "mac_address": "fc3fdb-29153e",
                    "port_id": "Trk8",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1c666d-9733f1",
                    "mac_address": "1c666d-9733f1",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb8c9e",
                    "mac_address": "08000f-cb8c9e",
                    "port_id": "F2",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000123-42b307",
                    "mac_address": "000123-42b307",
                    "port_id": "Trk144",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/08000f-cb9a25",
                    "mac_address": "08000f-cb9a25",
                    "port_id": "E17",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/000e6b-0a09e5",
                    "mac_address": "000e6b-0a09e5",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/38b19e-900020",
                    "mac_address": "38b19e-900020",
                    "port_id": "C11",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/00206b-0002d4",
                    "mac_address": "00206b-0002d4",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 599
                },
                {
                    "uri": "/mac-table/000105-541ffb",
                    "mac_address": "000105-541ffb",
                    "port_id": "Trk10",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 3003
                },
                {
                    "uri": "/mac-table/000105-41f74b",
                    "mac_address": "000105-41f74b",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/f860f0-b84d00",
                    "mac_address": "f860f0-b84d00",
                    "port_id": "Trk2",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/1400e9-0a7399",
                    "mac_address": "1400e9-0a7399",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/089204-bd5545",
                    "mac_address": "089204-bd5545",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/3024a9-78ddc4",
                    "mac_address": "3024a9-78ddc4",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/f860f0-b84d00",
                    "mac_address": "f860f0-b84d00",
                    "port_id": "Trk2",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/18e829-9cacb5",
                    "mac_address": "18e829-9cacb5",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-ce1610",
                    "mac_address": "08000f-ce1610",
                    "port_id": "E15",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000c29-b03586",
                    "mac_address": "000c29-b03586",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/b4fbe4-c364ce",
                    "mac_address": "b4fbe4-c364ce",
                    "port_id": "Trk2",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/005056-bf8b50",
                    "mac_address": "005056-bf8b50",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/7483c2-cc41c0",
                    "mac_address": "7483c2-cc41c0",
                    "port_id": "Trk8",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/000105-43bf34",
                    "mac_address": "000105-43bf34",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/80ee73-eae519",
                    "mac_address": "80ee73-eae519",
                    "port_id": "Trk3",
                    "vlan_id": 10
                },
                {
                    "uri": "/mac-table/0022d1-0405bc",
                    "mac_address": "0022d1-0405bc",
                    "port_id": "Trk144",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/38b19e-900061",
                    "mac_address": "38b19e-900061",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/00206b-00073c",
                    "mac_address": "00206b-00073c",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/865715-f9ebec",
                    "mac_address": "865715-f9ebec",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-cb9717",
                    "mac_address": "08000f-cb9717",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb947b",
                    "mac_address": "08000f-cb947b",
                    "port_id": "G1",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/887598-621f97",
                    "mac_address": "887598-621f97",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-cb9a03",
                    "mac_address": "08000f-cb9a03",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb92e7",
                    "mac_address": "08000f-cb92e7",
                    "port_id": "Trk7",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/0021b7-a752c0",
                    "mac_address": "0021b7-a752c0",
                    "port_id": "Trk3",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/5e2d54-1a6e73",
                    "mac_address": "5e2d54-1a6e73",
                    "port_id": "Trk9",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/48ba4e-ebc82c",
                    "mac_address": "48ba4e-ebc82c",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/b4fbe4-c35779",
                    "mac_address": "b4fbe4-c35779",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/c84f86-fc000a",
                    "mac_address": "c84f86-fc000a",
                    "port_id": "Trk144",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b4fbe4-c3649c",
                    "mac_address": "b4fbe4-c3649c",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/00206b-0002c6",
                    "mac_address": "00206b-0002c6",
                    "port_id": "Trk7",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/b4fbe4-c36174",
                    "mac_address": "b4fbe4-c36174",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/b4fbe4-c36883",
                    "mac_address": "b4fbe4-c36883",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/602232-25b51d",
                    "mac_address": "602232-25b51d",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/901b0e-d94f61",
                    "mac_address": "901b0e-d94f61",
                    "port_id": "E6",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-9af519",
                    "mac_address": "005056-9af519",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/005056-bf8b78",
                    "mac_address": "005056-bf8b78",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/089204-bd550a",
                    "mac_address": "089204-bd550a",
                    "port_id": "A18",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/f492bf-c38575",
                    "mac_address": "f492bf-c38575",
                    "port_id": "B20",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/000105-43bf32",
                    "mac_address": "000105-43bf32",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/001a8c-f0ca44",
                    "mac_address": "001a8c-f0ca44",
                    "port_id": "B19",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/68d79a-dcb030",
                    "mac_address": "68d79a-dcb030",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/c84f86-055e17",
                    "mac_address": "c84f86-055e17",
                    "port_id": "Trk144",
                    "vlan_id": 3901
                },
                {
                    "uri": "/mac-table/74fe48-3bedb1",
                    "mac_address": "74fe48-3bedb1",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/18e829-9cf494",
                    "mac_address": "18e829-9cf494",
                    "port_id": "Trk4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1730
                },
                {
                    "uri": "/mac-table/448a5b-757ef0",
                    "mac_address": "448a5b-757ef0",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/04b167-0ec0aa",
                    "mac_address": "04b167-0ec0aa",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/d61270-4bac70",
                    "mac_address": "d61270-4bac70",
                    "port_id": "Trk8",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/002324-059c75",
                    "mac_address": "002324-059c75",
                    "port_id": "Trk2",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/74d435-9a2948",
                    "mac_address": "74d435-9a2948",
                    "port_id": "Trk1",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/000054-fcde86",
                    "mac_address": "000054-fcde86",
                    "port_id": "A16",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/b49691-58b39d",
                    "mac_address": "b49691-58b39d",
                    "port_id": "Trk1",
                    "vlan_id": 10
                },
                {
                    "uri": "/mac-table/08000f-cb947a",
                    "mac_address": "08000f-cb947a",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/00085d-9a56fe",
                    "mac_address": "00085d-9a56fe",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/001cf7-3e0fb0",
                    "mac_address": "001cf7-3e0fb0",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/000105-43bf36",
                    "mac_address": "000105-43bf36",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/005056-ae466a",
                    "mac_address": "005056-ae466a",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/08000f-cb9c12",
                    "mac_address": "08000f-cb9c12",
                    "port_id": "Trk2",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 3053
                },
                {
                    "uri": "/mac-table/00a003-ec9205",
                    "mac_address": "00a003-ec9205",
                    "port_id": "Trk2",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/005056-bf1400",
                    "mac_address": "005056-bf1400",
                    "port_id": "Trk144",
                    "vlan_id": 2003
                },
                {
                    "uri": "/mac-table/08000f-cb9c51",
                    "mac_address": "08000f-cb9c51",
                    "port_id": "B8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/80ee73-c1b3f5",
                    "mac_address": "80ee73-c1b3f5",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/8030e0-845700",
                    "mac_address": "8030e0-845700",
                    "port_id": "Trk9",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/448a5b-a23928",
                    "mac_address": "448a5b-a23928",
                    "port_id": "F2",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 563
                },
                {
                    "uri": "/mac-table/305890-c658c6",
                    "mac_address": "305890-c658c6",
                    "port_id": "Trk3",
                    "vlan_id": 3051
                },
                {
                    "uri": "/mac-table/08000f-ce16ba",
                    "mac_address": "08000f-ce16ba",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/0090e8-0a0c84",
                    "mac_address": "0090e8-0a0c84",
                    "port_id": "Trk6",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/38b19e-900058",
                    "mac_address": "38b19e-900058",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/00074d-574a7a",
                    "mac_address": "00074d-574a7a",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/b827eb-47659e",
                    "mac_address": "b827eb-47659e",
                    "port_id": "Trk6",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/38b19e-9000d8",
                    "mac_address": "38b19e-9000d8",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/08000f-cb9c52",
                    "mac_address": "08000f-cb9c52",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/002406-f3a5b1",
                    "mac_address": "002406-f3a5b1",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/00be43-f9eb22",
                    "mac_address": "00be43-f9eb22",
                    "port_id": "Trk4",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00c0b7-4dea3c",
                    "mac_address": "00c0b7-4dea3c",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/08000f-cb98f0",
                    "mac_address": "08000f-cb98f0",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/0025ab-ad8775",
                    "mac_address": "0025ab-ad8775",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/005056-ae2105",
                    "mac_address": "005056-ae2105",
                    "port_id": "Trk144",
                    "vlan_id": 103
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/80ee73-ea27b8",
                    "mac_address": "80ee73-ea27b8",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb9bd7",
                    "mac_address": "08000f-cb9bd7",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/6c4b90-d5714e",
                    "mac_address": "6c4b90-d5714e",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb98a0",
                    "mac_address": "08000f-cb98a0",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/e2e86e-51f96e",
                    "mac_address": "e2e86e-51f96e",
                    "port_id": "Trk8",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/005056-bfcf76",
                    "mac_address": "005056-bfcf76",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/824746-e08b08",
                    "mac_address": "824746-e08b08",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3901
                },
                {
                    "uri": "/mac-table/08000f-cb9c38",
                    "mac_address": "08000f-cb9c38",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/f4939f-f38255",
                    "mac_address": "f4939f-f38255",
                    "port_id": "Trk1",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/3ec6d1-eb4c02",
                    "mac_address": "3ec6d1-eb4c02",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-9000ce",
                    "mac_address": "38b19e-9000ce",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 10
                },
                {
                    "uri": "/mac-table/001dc1-504536",
                    "mac_address": "001dc1-504536",
                    "port_id": "Trk144",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3014
                },
                {
                    "uri": "/mac-table/f4939f-f2ad6c",
                    "mac_address": "f4939f-f2ad6c",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00c0b7-ca3b3f",
                    "mac_address": "00c0b7-ca3b3f",
                    "port_id": "B6",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/001dc1-505441",
                    "mac_address": "001dc1-505441",
                    "port_id": "Trk4",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/fed351-c501e9",
                    "mac_address": "fed351-c501e9",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/64d1a3-40bf6f",
                    "mac_address": "64d1a3-40bf6f",
                    "port_id": "D5",
                    "vlan_id": 3903
                },
                {
                    "uri": "/mac-table/18e829-9cacb5",
                    "mac_address": "18e829-9cacb5",
                    "port_id": "Trk4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/38b19e-90003c",
                    "mac_address": "38b19e-90003c",
                    "port_id": "Trk10",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/80ee73-c45c43",
                    "mac_address": "80ee73-c45c43",
                    "port_id": "Trk1",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/62fb50-e42165",
                    "mac_address": "62fb50-e42165",
                    "port_id": "Trk6",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/70b5e8-f83603",
                    "mac_address": "70b5e8-f83603",
                    "port_id": "G6",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000105-43bf14",
                    "mac_address": "000105-43bf14",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/c84bd6-11d63f",
                    "mac_address": "c84bd6-11d63f",
                    "port_id": "Trk4",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/b827eb-9ea34a",
                    "mac_address": "b827eb-9ea34a",
                    "port_id": "Trk6",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/08000f-f47f59",
                    "mac_address": "08000f-f47f59",
                    "port_id": "B12",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/6cc217-a5a740",
                    "mac_address": "6cc217-a5a740",
                    "port_id": "Trk4",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1400e9-0a7631",
                    "mac_address": "1400e9-0a7631",
                    "port_id": "E8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/000105-43bf26",
                    "mac_address": "000105-43bf26",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/c83ea7-02625f",
                    "mac_address": "c83ea7-02625f",
                    "port_id": "C19",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/000105-4025e3",
                    "mac_address": "000105-4025e3",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-cb99b5",
                    "mac_address": "08000f-cb99b5",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/00206b-a2924e",
                    "mac_address": "00206b-a2924e",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/00016c-d1a595",
                    "mac_address": "00016c-d1a595",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/38b19e-9001df",
                    "mac_address": "38b19e-9001df",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/44a56e-3b4d40",
                    "mac_address": "44a56e-3b4d40",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-f47f55",
                    "mac_address": "08000f-f47f55",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/002324-059d43",
                    "mac_address": "002324-059d43",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/7c5a1c-cb712f",
                    "mac_address": "7c5a1c-cb712f",
                    "port_id": "Trk144",
                    "vlan_id": 3904
                },
                {
                    "uri": "/mac-table/005056-b46a0a",
                    "mac_address": "005056-b46a0a",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/6cc217-a6bdc0",
                    "mac_address": "6cc217-a6bdc0",
                    "port_id": "Trk7",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/0090e8-750068",
                    "mac_address": "0090e8-750068",
                    "port_id": "Trk8",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/64cba3-f18249",
                    "mac_address": "64cba3-f18249",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/000c29-d743e5",
                    "mac_address": "000c29-d743e5",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/089204-bd554b",
                    "mac_address": "089204-bd554b",
                    "port_id": "G1",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/00196f-101e48",
                    "mac_address": "00196f-101e48",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/0050b6-aff015",
                    "mac_address": "0050b6-aff015",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb990f",
                    "mac_address": "08000f-cb990f",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/d02788-869b73",
                    "mac_address": "d02788-869b73",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/868677-e2400f",
                    "mac_address": "868677-e2400f",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/08000f-cb9a4a",
                    "mac_address": "08000f-cb9a4a",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bf00af",
                    "mac_address": "005056-bf00af",
                    "port_id": "A21",
                    "vlan_id": 254
                },
                {
                    "uri": "/mac-table/f4939f-f382ff",
                    "mac_address": "f4939f-f382ff",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/70b3d5-2e3044",
                    "mac_address": "70b3d5-2e3044",
                    "port_id": "Trk8",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/38b19e-900056",
                    "mac_address": "38b19e-900056",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/448a5b-54478d",
                    "mac_address": "448a5b-54478d",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3051
                },
                {
                    "uri": "/mac-table/f23161-1f0c63",
                    "mac_address": "f23161-1f0c63",
                    "port_id": "Trk7",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/b4fbe4-c35d30",
                    "mac_address": "b4fbe4-c35d30",
                    "port_id": "Trk2",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/18e829-e01b53",
                    "mac_address": "18e829-e01b53",
                    "port_id": "Trk144",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/f492bf-c3876f",
                    "mac_address": "f492bf-c3876f",
                    "port_id": "Trk7",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/000105-43befc",
                    "mac_address": "000105-43befc",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/000e6b-0a22f6",
                    "mac_address": "000e6b-0a22f6",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/d02788-8fc6ce",
                    "mac_address": "d02788-8fc6ce",
                    "port_id": "Trk1",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb9b87",
                    "mac_address": "08000f-cb9b87",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/76d149-9c2b97",
                    "mac_address": "76d149-9c2b97",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-cb8d6e",
                    "mac_address": "08000f-cb8d6e",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb989f",
                    "mac_address": "08000f-cb989f",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b831b5-42bdb0",
                    "mac_address": "b831b5-42bdb0",
                    "port_id": "Trk4",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/32e5e3-c14a17",
                    "mac_address": "32e5e3-c14a17",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/089204-bd54fd",
                    "mac_address": "089204-bd54fd",
                    "port_id": "C12",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/104f58-162280",
                    "mac_address": "104f58-162280",
                    "port_id": "Trk10",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/68d79a-dc8f14",
                    "mac_address": "68d79a-dc8f14",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/00e04c-6c35d3",
                    "mac_address": "00e04c-6c35d3",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/001114-0e3785",
                    "mac_address": "001114-0e3785",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb8908",
                    "mac_address": "08000f-cb8908",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-ae72a2",
                    "mac_address": "005056-ae72a2",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/c89cdc-34f8b7",
                    "mac_address": "c89cdc-34f8b7",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-900055",
                    "mac_address": "38b19e-900055",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/00206b-a2923c",
                    "mac_address": "00206b-a2923c",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/68d79a-dcb9b2",
                    "mac_address": "68d79a-dcb9b2",
                    "port_id": "Trk2",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/00074d-901350",
                    "mac_address": "00074d-901350",
                    "port_id": "Trk3",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/14dae9-4321e8",
                    "mac_address": "14dae9-4321e8",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/38b19e-9000cb",
                    "mac_address": "38b19e-9000cb",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/6cc217-a53580",
                    "mac_address": "6cc217-a53580",
                    "port_id": "Trk1",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/000105-43befa",
                    "mac_address": "000105-43befa",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/d43d7e-7995aa",
                    "mac_address": "d43d7e-7995aa",
                    "port_id": "Trk144",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 20
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 552
                },
                {
                    "uri": "/mac-table/000105-41f747",
                    "mac_address": "000105-41f747",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3101
                },
                {
                    "uri": "/mac-table/005056-bf5143",
                    "mac_address": "005056-bf5143",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/6c4b90-2f1d0f",
                    "mac_address": "6c4b90-2f1d0f",
                    "port_id": "Trk2",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/38b19e-9001dc",
                    "mac_address": "38b19e-9001dc",
                    "port_id": "Trk9",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/e4a7c5-44d046",
                    "mac_address": "e4a7c5-44d046",
                    "port_id": "Trk7",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-f4b444",
                    "mac_address": "08000f-f4b444",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/46da90-439888",
                    "mac_address": "46da90-439888",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/002406-f3a5e3",
                    "mac_address": "002406-f3a5e3",
                    "port_id": "Trk2",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/08000f-cb9a44",
                    "mac_address": "08000f-cb9a44",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/002406-f41db3",
                    "mac_address": "002406-f41db3",
                    "port_id": "Trk2",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/56d478-82b986",
                    "mac_address": "56d478-82b986",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/6cc217-a63c40",
                    "mac_address": "6cc217-a63c40",
                    "port_id": "Trk6",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "Trk144",
                    "vlan_id": 2033
                },
                {
                    "uri": "/mac-table/806d97-1953dc",
                    "mac_address": "806d97-1953dc",
                    "port_id": "C9",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/f4939f-f2ae29",
                    "mac_address": "f4939f-f2ae29",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000105-43bf40",
                    "mac_address": "000105-43bf40",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1c6f65-80719f",
                    "mac_address": "1c6f65-80719f",
                    "port_id": "Trk3",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/f492bf-c3847a",
                    "mac_address": "f492bf-c3847a",
                    "port_id": "Trk144",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/38b19e-9000b7",
                    "mac_address": "38b19e-9000b7",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/2cf05d-14fcdc",
                    "mac_address": "2cf05d-14fcdc",
                    "port_id": "E15",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb8c7c",
                    "mac_address": "08000f-cb8c7c",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb9c40",
                    "mac_address": "08000f-cb9c40",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 511
                },
                {
                    "uri": "/mac-table/18e829-e01b53",
                    "mac_address": "18e829-e01b53",
                    "port_id": "Trk1",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/08000f-cb8c50",
                    "mac_address": "08000f-cb8c50",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/34298f-77bec2",
                    "mac_address": "34298f-77bec2",
                    "port_id": "Trk4",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-bff54f",
                    "mac_address": "005056-bff54f",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 20
                },
                {
                    "uri": "/mac-table/66562a-3bfd8f",
                    "mac_address": "66562a-3bfd8f",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/b827eb-ef3590",
                    "mac_address": "b827eb-ef3590",
                    "port_id": "Trk4",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/38b19e-900100",
                    "mac_address": "38b19e-900100",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/d8b122-a9f480",
                    "mac_address": "d8b122-a9f480",
                    "port_id": "D3",
                    "vlan_id": 3902
                },
                {
                    "uri": "/mac-table/56d490-680b34",
                    "mac_address": "56d490-680b34",
                    "port_id": "B20",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/001dc1-503d66",
                    "mac_address": "001dc1-503d66",
                    "port_id": "Trk9",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000c29-4e7d01",
                    "mac_address": "000c29-4e7d01",
                    "port_id": "A21",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/000123-340b3b",
                    "mac_address": "000123-340b3b",
                    "port_id": "Trk1",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/08000f-cb9a42",
                    "mac_address": "08000f-cb9a42",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb85c3",
                    "mac_address": "08000f-cb85c3",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/6c4b90-a97f14",
                    "mac_address": "6c4b90-a97f14",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb9c2a",
                    "mac_address": "08000f-cb9c2a",
                    "port_id": "Trk7",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/000105-260027",
                    "mac_address": "000105-260027",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/001a8c-f0ca40",
                    "mac_address": "001a8c-f0ca40",
                    "port_id": "C1",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/001dc1-503d1d",
                    "mac_address": "001dc1-503d1d",
                    "port_id": "Trk6",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/80ee73-e261c1",
                    "mac_address": "80ee73-e261c1",
                    "port_id": "Trk8",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/08000f-ce1602",
                    "mac_address": "08000f-ce1602",
                    "port_id": "E19",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-df1fd2",
                    "mac_address": "08000f-df1fd2",
                    "port_id": "E3",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/50dad6-bf1625",
                    "mac_address": "50dad6-bf1625",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-df1fd7",
                    "mac_address": "08000f-df1fd7",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bf56b1",
                    "mac_address": "005056-bf56b1",
                    "port_id": "B21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/001a8c-f0ca47",
                    "mac_address": "001a8c-f0ca47",
                    "port_id": "B3",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/601466-6261e4",
                    "mac_address": "601466-6261e4",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 50
                },
                {
                    "uri": "/mac-table/000105-41f73f",
                    "mac_address": "000105-41f73f",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-cb97fb",
                    "mac_address": "08000f-cb97fb",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/08000f-cb8c68",
                    "mac_address": "08000f-cb8c68",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/80ee73-e2635c",
                    "mac_address": "80ee73-e2635c",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/08000f-cb90b4",
                    "mac_address": "08000f-cb90b4",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 91
                },
                {
                    "uri": "/mac-table/000105-41f74e",
                    "mac_address": "000105-41f74e",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/002406-f38512",
                    "mac_address": "002406-f38512",
                    "port_id": "Trk1",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/08000f-cb9828",
                    "mac_address": "08000f-cb9828",
                    "port_id": "A19",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 254
                },
                {
                    "uri": "/mac-table/000db9-566645",
                    "mac_address": "000db9-566645",
                    "port_id": "Trk2",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-ce15f9",
                    "mac_address": "08000f-ce15f9",
                    "port_id": "Trk144",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bf5f4c",
                    "mac_address": "005056-bf5f4c",
                    "port_id": "Trk144",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/00074d-3880a4",
                    "mac_address": "00074d-3880a4",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1010
                },
                {
                    "uri": "/mac-table/08000f-df1fd2",
                    "mac_address": "08000f-df1fd2",
                    "port_id": "E3",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 562
                },
                {
                    "uri": "/mac-table/000105-389618",
                    "mac_address": "000105-389618",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/b827eb-99170e",
                    "mac_address": "b827eb-99170e",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-ae1f27",
                    "mac_address": "005056-ae1f27",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/2e3f0d-15b8cd",
                    "mac_address": "2e3f0d-15b8cd",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/00be43-f9ecd2",
                    "mac_address": "00be43-f9ecd2",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/d25868-fb1272",
                    "mac_address": "d25868-fb1272",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/000c29-989213",
                    "mac_address": "000c29-989213",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/4827ea-d2a200",
                    "mac_address": "4827ea-d2a200",
                    "port_id": "Trk3",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-90005c",
                    "mac_address": "38b19e-90005c",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/bcaec5-92990b",
                    "mac_address": "bcaec5-92990b",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-9000d7",
                    "mac_address": "38b19e-9000d7",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/0090e8-762247",
                    "mac_address": "0090e8-762247",
                    "port_id": "D18",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/000105-41f745",
                    "mac_address": "000105-41f745",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/001dc1-7146d6",
                    "mac_address": "001dc1-7146d6",
                    "port_id": "Trk9",
                    "vlan_id": 71
                },
                {
                    "uri": "/mac-table/f8d027-2ea66b",
                    "mac_address": "f8d027-2ea66b",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/6cc217-a62740",
                    "mac_address": "6cc217-a62740",
                    "port_id": "Trk2",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 81
                },
                {
                    "uri": "/mac-table/08000f-f48d2d",
                    "mac_address": "08000f-f48d2d",
                    "port_id": "A9",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/806d97-2f8c06",
                    "mac_address": "806d97-2f8c06",
                    "port_id": "Trk7",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/000105-49cef1",
                    "mac_address": "000105-49cef1",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/38b19e-9000bc",
                    "mac_address": "38b19e-9000bc",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/e28be3-aa1b24",
                    "mac_address": "e28be3-aa1b24",
                    "port_id": "Trk4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/003064-2a583f",
                    "mac_address": "003064-2a583f",
                    "port_id": "Trk4",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/00206b-000339",
                    "mac_address": "00206b-000339",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1020
                },
                {
                    "uri": "/mac-table/e2afbd-b776e5",
                    "mac_address": "e2afbd-b776e5",
                    "port_id": "B20",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/089204-bbea4c",
                    "mac_address": "089204-bbea4c",
                    "port_id": "F4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/00be43-f9eec2",
                    "mac_address": "00be43-f9eec2",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/68d79a-dcbb71",
                    "mac_address": "68d79a-dcbb71",
                    "port_id": "Trk3",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/f47b09-b33e9f",
                    "mac_address": "f47b09-b33e9f",
                    "port_id": "Trk9",
                    "vlan_id": 3051
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/3003c8-ffa669",
                    "mac_address": "3003c8-ffa669",
                    "port_id": "Trk4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/002406-f41db6",
                    "mac_address": "002406-f41db6",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/7c5a1c-cb60fb",
                    "mac_address": "7c5a1c-cb60fb",
                    "port_id": "Trk30",
                    "vlan_id": 3905
                },
                {
                    "uri": "/mac-table/08000f-cb9c51",
                    "mac_address": "08000f-cb9c51",
                    "port_id": "B8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/00085d-5c0b32",
                    "mac_address": "00085d-5c0b32",
                    "port_id": "D12",
                    "vlan_id": 561
                },
                {
                    "uri": "/mac-table/7ca177-36a2c9",
                    "mac_address": "7ca177-36a2c9",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-df1fd5",
                    "mac_address": "08000f-df1fd5",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/74fe48-397188",
                    "mac_address": "74fe48-397188",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/005056-bf2a26",
                    "mac_address": "005056-bf2a26",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-ce1600",
                    "mac_address": "08000f-ce1600",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/38b19e-900052",
                    "mac_address": "38b19e-900052",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-cb87fd",
                    "mac_address": "08000f-cb87fd",
                    "port_id": "Trk10",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/c83ea7-02a7a2",
                    "mac_address": "c83ea7-02a7a2",
                    "port_id": "C19",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 3051
                },
                {
                    "uri": "/mac-table/000105-389652",
                    "mac_address": "000105-389652",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/08000f-d1294c",
                    "mac_address": "08000f-d1294c",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-ce0fa0",
                    "mac_address": "08000f-ce0fa0",
                    "port_id": "Trk6",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb8c58",
                    "mac_address": "08000f-cb8c58",
                    "port_id": "Trk7",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/eaf286-5b1e08",
                    "mac_address": "eaf286-5b1e08",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/026702-d81e4c",
                    "mac_address": "026702-d81e4c",
                    "port_id": "Trk4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/80ee73-edd6fa",
                    "mac_address": "80ee73-edd6fa",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/005056-bfc0ce",
                    "mac_address": "005056-bfc0ce",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/6c4b90-363645",
                    "mac_address": "6c4b90-363645",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-f4b436",
                    "mac_address": "08000f-f4b436",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/002406-f32c2a",
                    "mac_address": "002406-f32c2a",
                    "port_id": "Trk3",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/8c3bad-65c272",
                    "mac_address": "8c3bad-65c272",
                    "port_id": "Trk144",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/d43d7e-7992e1",
                    "mac_address": "d43d7e-7992e1",
                    "port_id": "Trk1",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/08000f-cb8c74",
                    "mac_address": "08000f-cb8c74",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/002406-f32c2f",
                    "mac_address": "002406-f32c2f",
                    "port_id": "Trk9",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/12cdde-01ba9c",
                    "mac_address": "12cdde-01ba9c",
                    "port_id": "Trk4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/000105-520150",
                    "mac_address": "000105-520150",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/9009d0-1bac2d",
                    "mac_address": "9009d0-1bac2d",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/08000f-ec02ea",
                    "mac_address": "08000f-ec02ea",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb95e7",
                    "mac_address": "08000f-cb95e7",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/7483c2-cc404b",
                    "mac_address": "7483c2-cc404b",
                    "port_id": "Trk2",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/38b19e-9000c9",
                    "mac_address": "38b19e-9000c9",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d457ca",
                    "mac_address": "1458d0-d457ca",
                    "port_id": "Trk144",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 561
                },
                {
                    "uri": "/mac-table/74fe48-35e3e0",
                    "mac_address": "74fe48-35e3e0",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 511
                },
                {
                    "uri": "/mac-table/806d97-2f8ca0",
                    "mac_address": "806d97-2f8ca0",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/18e829-e00f70",
                    "mac_address": "18e829-e00f70",
                    "port_id": "Trk6",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 561
                },
                {
                    "uri": "/mac-table/0021b7-a7a2c7",
                    "mac_address": "0021b7-a7a2c7",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/b00cd1-a14a75",
                    "mac_address": "b00cd1-a14a75",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/74fe48-3dee53",
                    "mac_address": "74fe48-3dee53",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/000105-4cf7ac",
                    "mac_address": "000105-4cf7ac",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/68d79a-dc8d3a",
                    "mac_address": "68d79a-dc8d3a",
                    "port_id": "Trk4",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/38b19e-9000ac",
                    "mac_address": "38b19e-9000ac",
                    "port_id": "Trk2",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 599
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 30
                },
                {
                    "uri": "/mac-table/00085d-9a56a0",
                    "mac_address": "00085d-9a56a0",
                    "port_id": "F20",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/003064-291991",
                    "mac_address": "003064-291991",
                    "port_id": "Trk6",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/00206b-e2b203",
                    "mac_address": "00206b-e2b203",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/c83ea7-01ae78",
                    "mac_address": "c83ea7-01ae78",
                    "port_id": "Trk2",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-cb98f0",
                    "mac_address": "08000f-cb98f0",
                    "port_id": "A13",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/4cd577-ae3993",
                    "mac_address": "4cd577-ae3993",
                    "port_id": "Trk4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/005056-bffa8e",
                    "mac_address": "005056-bffa8e",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/38b19e-90004f",
                    "mac_address": "38b19e-90004f",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/008092-464d37",
                    "mac_address": "008092-464d37",
                    "port_id": "B5",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/001a8c-f0ca44",
                    "mac_address": "001a8c-f0ca44",
                    "port_id": "B19",
                    "vlan_id": 2033
                },
                {
                    "uri": "/mac-table/08000f-cb9c4b",
                    "mac_address": "08000f-cb9c4b",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/b42e99-2713a1",
                    "mac_address": "b42e99-2713a1",
                    "port_id": "Trk6",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/b827eb-388012",
                    "mac_address": "b827eb-388012",
                    "port_id": "Trk4",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/38b19e-90035f",
                    "mac_address": "38b19e-90035f",
                    "port_id": "Trk144",
                    "vlan_id": 20
                },
                {
                    "uri": "/mac-table/4c5262-af132f",
                    "mac_address": "4c5262-af132f",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/7483c2-cc4210",
                    "mac_address": "7483c2-cc4210",
                    "port_id": "Trk144",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38f3ab-f61105",
                    "mac_address": "38f3ab-f61105",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb972b",
                    "mac_address": "08000f-cb972b",
                    "port_id": "E12",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 81
                },
                {
                    "uri": "/mac-table/08000f-cb9828",
                    "mac_address": "08000f-cb9828",
                    "port_id": "A19",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/089204-bd5533",
                    "mac_address": "089204-bd5533",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/806d97-315469",
                    "mac_address": "806d97-315469",
                    "port_id": "Trk7",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 103
                },
                {
                    "uri": "/mac-table/08000f-ce07c5",
                    "mac_address": "08000f-ce07c5",
                    "port_id": "Trk2",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bfcddb",
                    "mac_address": "005056-bfcddb",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/90b11c-53c74e",
                    "mac_address": "90b11c-53c74e",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/d06726-80fbc0",
                    "mac_address": "d06726-80fbc0",
                    "port_id": "Trk8",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/0090e8-7669e3",
                    "mac_address": "0090e8-7669e3",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00206b-482f87",
                    "mac_address": "00206b-482f87",
                    "port_id": "Trk144",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/00085d-5c0b32",
                    "mac_address": "00085d-5c0b32",
                    "port_id": "D12",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/0021b7-2fe235",
                    "mac_address": "0021b7-2fe235",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 552
                },
                {
                    "uri": "/mac-table/0000b4-d2a530",
                    "mac_address": "0000b4-d2a530",
                    "port_id": "Trk144",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/005056-bfae9a",
                    "mac_address": "005056-bfae9a",
                    "port_id": "B21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/38b19e-900021",
                    "mac_address": "38b19e-900021",
                    "port_id": "C11",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/6c4b90-d87eaa",
                    "mac_address": "6c4b90-d87eaa",
                    "port_id": "Trk10",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/00206b-009bd1",
                    "mac_address": "00206b-009bd1",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/288088-e7dea5",
                    "mac_address": "288088-e7dea5",
                    "port_id": "A13",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/00206b-4e17c0",
                    "mac_address": "00206b-4e17c0",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/0090e8-0bf030",
                    "mac_address": "0090e8-0bf030",
                    "port_id": "D15",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/f492bf-c3876f",
                    "mac_address": "f492bf-c3876f",
                    "port_id": "Trk7",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/806d97-2f8c59",
                    "mac_address": "806d97-2f8c59",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb97fb",
                    "mac_address": "08000f-cb97fb",
                    "port_id": "A13",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-f48b44",
                    "mac_address": "08000f-f48b44",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-bf1a4f",
                    "mac_address": "005056-bf1a4f",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/9020c2-482c40",
                    "mac_address": "9020c2-482c40",
                    "port_id": "Trk9",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/005056-bf8c4e",
                    "mac_address": "005056-bf8c4e",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1acb9e-ddda37",
                    "mac_address": "1acb9e-ddda37",
                    "port_id": "B20",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/b206da-06fdf3",
                    "mac_address": "b206da-06fdf3",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/00206b-000913",
                    "mac_address": "00206b-000913",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/b04f13-25746c",
                    "mac_address": "b04f13-25746c",
                    "port_id": "E8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/940e6b-8d3be7",
                    "mac_address": "940e6b-8d3be7",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1ca0b8-74bd5f",
                    "mac_address": "1ca0b8-74bd5f",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000c29-ea173b",
                    "mac_address": "000c29-ea173b",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/d88083-b421f1",
                    "mac_address": "d88083-b421f1",
                    "port_id": "C4",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/005056-bff303",
                    "mac_address": "005056-bff303",
                    "port_id": "B21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/62867a-a41b18",
                    "mac_address": "62867a-a41b18",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-9000cd",
                    "mac_address": "38b19e-9000cd",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/005056-bf8ffc",
                    "mac_address": "005056-bf8ffc",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/e0aa96-87e70a",
                    "mac_address": "e0aa96-87e70a",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/74fe48-35e37b",
                    "mac_address": "74fe48-35e37b",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/00908f-b877db",
                    "mac_address": "00908f-b877db",
                    "port_id": "F20",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/9c5a81-b423a0",
                    "mac_address": "9c5a81-b423a0",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/00206b-00974a",
                    "mac_address": "00206b-00974a",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/08000f-df1de9",
                    "mac_address": "08000f-df1de9",
                    "port_id": "Trk7",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/dcfb02-be1282",
                    "mac_address": "dcfb02-be1282",
                    "port_id": "E9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/0050b6-dd8758",
                    "mac_address": "0050b6-dd8758",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-ce15db",
                    "mac_address": "08000f-ce15db",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb9754",
                    "mac_address": "08000f-cb9754",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/005056-ae0e7a",
                    "mac_address": "005056-ae0e7a",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/74fe48-397782",
                    "mac_address": "74fe48-397782",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/08000f-cb9b2d",
                    "mac_address": "08000f-cb9b2d",
                    "port_id": "Trk10",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/164e0a-b2f500",
                    "mac_address": "164e0a-b2f500",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/000105-260025",
                    "mac_address": "000105-260025",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/38b19e-90005b",
                    "mac_address": "38b19e-90005b",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/b4fbe4-c36174",
                    "mac_address": "b4fbe4-c36174",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/000792-509406",
                    "mac_address": "000792-509406",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/b42e99-271441",
                    "mac_address": "b42e99-271441",
                    "port_id": "Trk4",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/f492bf-c38575",
                    "mac_address": "f492bf-c38575",
                    "port_id": "B20",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-cb921b",
                    "mac_address": "08000f-cb921b",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/d06726-753180",
                    "mac_address": "d06726-753180",
                    "port_id": "Trk8",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/448a5b-fca196",
                    "mac_address": "448a5b-fca196",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/005056-9a1b02",
                    "mac_address": "005056-9a1b02",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 563
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/08000f-ec04a3",
                    "mac_address": "08000f-ec04a3",
                    "port_id": "E6",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 591
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 3052
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/288023-6f0540",
                    "mac_address": "288023-6f0540",
                    "port_id": "Trk9",
                    "vlan_id": 502
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 92
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 1720
                },
                {
                    "uri": "/mac-table/00074d-883a4b",
                    "mac_address": "00074d-883a4b",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/4678af-79c690",
                    "mac_address": "4678af-79c690",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/000c29-95444d",
                    "mac_address": "000c29-95444d",
                    "port_id": "A21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/74d435-fd8023",
                    "mac_address": "74d435-fd8023",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/08000f-cb9c4e",
                    "mac_address": "08000f-cb9c4e",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/c83ea7-01d008",
                    "mac_address": "c83ea7-01d008",
                    "port_id": "Trk2",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/b27355-42c8f7",
                    "mac_address": "b27355-42c8f7",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/62c003-290ea8",
                    "mac_address": "62c003-290ea8",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/005056-bf2f61",
                    "mac_address": "005056-bf2f61",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00206b-e2b067",
                    "mac_address": "00206b-e2b067",
                    "port_id": "Trk9",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/7483c2-cc4210",
                    "mac_address": "7483c2-cc4210",
                    "port_id": "Trk6",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/860eff-4da94e",
                    "mac_address": "860eff-4da94e",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-cb8bde",
                    "mac_address": "08000f-cb8bde",
                    "port_id": "E1",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb9809",
                    "mac_address": "08000f-cb9809",
                    "port_id": "C9",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/32a30b-3cd3a1",
                    "mac_address": "32a30b-3cd3a1",
                    "port_id": "Trk3",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/c84f86-fc0001",
                    "mac_address": "c84f86-fc0001",
                    "port_id": "Trk144",
                    "vlan_id": 90
                },
                {
                    "uri": "/mac-table/a0f3c1-3ed66f",
                    "mac_address": "a0f3c1-3ed66f",
                    "port_id": "Trk3",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/08000f-cb9a5a",
                    "mac_address": "08000f-cb9a5a",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/74fe48-29db1e",
                    "mac_address": "74fe48-29db1e",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/f0d5bf-ef912e",
                    "mac_address": "f0d5bf-ef912e",
                    "port_id": "Trk8",
                    "vlan_id": 3060
                },
                {
                    "uri": "/mac-table/08000f-ce16be",
                    "mac_address": "08000f-ce16be",
                    "port_id": "D14",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb8c6c",
                    "mac_address": "08000f-cb8c6c",
                    "port_id": "G2",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/38b19e-9000d9",
                    "mac_address": "38b19e-9000d9",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/64c901-a56ad8",
                    "mac_address": "64c901-a56ad8",
                    "port_id": "Trk10",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/005056-bf1c33",
                    "mac_address": "005056-bf1c33",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/003064-2c3af5",
                    "mac_address": "003064-2c3af5",
                    "port_id": "Trk2",
                    "vlan_id": 60
                },
                {
                    "uri": "/mac-table/34298f-77bf11",
                    "mac_address": "34298f-77bf11",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/000000-3be990",
                    "mac_address": "000000-3be990",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 2008
                },
                {
                    "uri": "/mac-table/005056-bf9918",
                    "mac_address": "005056-bf9918",
                    "port_id": "Trk144",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/08000f-f47f56",
                    "mac_address": "08000f-f47f56",
                    "port_id": "Trk4",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 590
                },
                {
                    "uri": "/mac-table/68d79a-dc8d3a",
                    "mac_address": "68d79a-dc8d3a",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1458d0-d44700",
                    "mac_address": "1458d0-d44700",
                    "port_id": "Trk144",
                    "vlan_id": 552
                },
                {
                    "uri": "/mac-table/80ee73-ea2565",
                    "mac_address": "80ee73-ea2565",
                    "port_id": "Trk9",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/005056-bfd837",
                    "mac_address": "005056-bfd837",
                    "port_id": "A21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/806d97-315e1e",
                    "mac_address": "806d97-315e1e",
                    "port_id": "B9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-df1fcf",
                    "mac_address": "08000f-df1fcf",
                    "port_id": "Trk9",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/08000f-cb9b81",
                    "mac_address": "08000f-cb9b81",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/c84f86-fc0009",
                    "mac_address": "c84f86-fc0009",
                    "port_id": "Trk144",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/000105-43bf28",
                    "mac_address": "000105-43bf28",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/38b19e-90010a",
                    "mac_address": "38b19e-90010a",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/e60ece-fb62aa",
                    "mac_address": "e60ece-fb62aa",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/1a0b4a-3cd521",
                    "mac_address": "1a0b4a-3cd521",
                    "port_id": "B20",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/c84f86-0575b7",
                    "mac_address": "c84f86-0575b7",
                    "port_id": "C1",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb8c6c",
                    "mac_address": "08000f-cb8c6c",
                    "port_id": "G2",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb8bde",
                    "mac_address": "08000f-cb8bde",
                    "port_id": "E1",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/00206b-e28d11",
                    "mac_address": "00206b-e28d11",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/08000f-cb9a4d",
                    "mac_address": "08000f-cb9a4d",
                    "port_id": "B9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-9000d6",
                    "mac_address": "38b19e-9000d6",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/1458d0-d457ea",
                    "mac_address": "1458d0-d457ea",
                    "port_id": "Trk144",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/0055da-3100c7",
                    "mac_address": "0055da-3100c7",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/005056-bf6e0b",
                    "mac_address": "005056-bf6e0b",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/368b27-98dcdf",
                    "mac_address": "368b27-98dcdf",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/38b19e-90004e",
                    "mac_address": "38b19e-90004e",
                    "port_id": "Trk3",
                    "vlan_id": 40
                },
                {
                    "uri": "/mac-table/482ae3-a46fd6",
                    "mac_address": "482ae3-a46fd6",
                    "port_id": "Trk8",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/004001-33c99a",
                    "mac_address": "004001-33c99a",
                    "port_id": "Trk2",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/089204-bd5535",
                    "mac_address": "089204-bd5535",
                    "port_id": "Trk8",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-9fffff",
                    "mac_address": "38b19e-9fffff",
                    "port_id": "Trk144",
                    "vlan_id": 20
                },
                {
                    "uri": "/mac-table/806d97-2f8cee",
                    "mac_address": "806d97-2f8cee",
                    "port_id": "Trk9",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-9000fe",
                    "mac_address": "38b19e-9000fe",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/005056-bf1f70",
                    "mac_address": "005056-bf1f70",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/f01faf-4f9831",
                    "mac_address": "f01faf-4f9831",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/e641d0-6207da",
                    "mac_address": "e641d0-6207da",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/66f06e-2ffade",
                    "mac_address": "66f06e-2ffade",
                    "port_id": "Trk8",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/74fe48-3df19d",
                    "mac_address": "74fe48-3df19d",
                    "port_id": "Trk1",
                    "vlan_id": 102
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 110
                },
                {
                    "uri": "/mac-table/68d79a-dcb1fe",
                    "mac_address": "68d79a-dcb1fe",
                    "port_id": "Trk9",
                    "vlan_id": 95
                },
                {
                    "uri": "/mac-table/005056-bf7fa8",
                    "mac_address": "005056-bf7fa8",
                    "port_id": "Trk144",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb947b",
                    "mac_address": "08000f-cb947b",
                    "port_id": "G1",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/8cd9d6-3b2220",
                    "mac_address": "8cd9d6-3b2220",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/b00cd1-f505d6",
                    "mac_address": "b00cd1-f505d6",
                    "port_id": "Trk8",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/001a8c-f0ca44",
                    "mac_address": "001a8c-f0ca44",
                    "port_id": "B19",
                    "vlan_id": 2025
                },
                {
                    "uri": "/mac-table/00206b-4e1867",
                    "mac_address": "00206b-4e1867",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/e23f4e-a96f77",
                    "mac_address": "e23f4e-a96f77",
                    "port_id": "Trk4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/acc906-0e96b1",
                    "mac_address": "acc906-0e96b1",
                    "port_id": "C4",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/0055da-311ae7",
                    "mac_address": "0055da-311ae7",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/00206b-000841",
                    "mac_address": "00206b-000841",
                    "port_id": "Trk144",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/000af7-5df353",
                    "mac_address": "000af7-5df353",
                    "port_id": "H22",
                    "vlan_id": 2040
                },
                {
                    "uri": "/mac-table/380aab-05be2c",
                    "mac_address": "380aab-05be2c",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/000105-41f74d",
                    "mac_address": "000105-41f74d",
                    "port_id": "Trk3",
                    "vlan_id": 1
                },
                {
                    "uri": "/mac-table/000c29-2729be",
                    "mac_address": "000c29-2729be",
                    "port_id": "B21",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/005056-bf7b68",
                    "mac_address": "005056-bf7b68",
                    "port_id": "B21",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb91b4",
                    "mac_address": "08000f-cb91b4",
                    "port_id": "A17",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/d43d7e-0dc223",
                    "mac_address": "d43d7e-0dc223",
                    "port_id": "Trk1",
                    "vlan_id": 512
                },
                {
                    "uri": "/mac-table/123f2e-2deefd",
                    "mac_address": "123f2e-2deefd",
                    "port_id": "Trk3",
                    "vlan_id": 3053
                },
                {
                    "uri": "/mac-table/c01803-d24355",
                    "mac_address": "c01803-d24355",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/38b19e-90010c",
                    "mac_address": "38b19e-90010c",
                    "port_id": "Trk1",
                    "vlan_id": 540
                },
                {
                    "uri": "/mac-table/00ce39-cf2545",
                    "mac_address": "00ce39-cf2545",
                    "port_id": "Trk9",
                    "vlan_id": 560
                },
                {
                    "uri": "/mac-table/005056-bfd911",
                    "mac_address": "005056-bfd911",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/005056-bf56bd",
                    "mac_address": "005056-bf56bd",
                    "port_id": "Trk144",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/001a8c-f0ca46",
                    "mac_address": "001a8c-f0ca46",
                    "port_id": "C2",
                    "vlan_id": 81
                },
                {
                    "uri": "/mac-table/a4ae11-0fbc4b",
                    "mac_address": "a4ae11-0fbc4b",
                    "port_id": "Trk4",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/08000f-cb9900",
                    "mac_address": "08000f-cb9900",
                    "port_id": "Trk8",
                    "vlan_id": 120
                },
                {
                    "uri": "/mac-table/9009d0-1baae3",
                    "mac_address": "9009d0-1baae3",
                    "port_id": "Trk8",
                    "vlan_id": 510
                },
                {
                    "uri": "/mac-table/0022d1-0406cd",
                    "mac_address": "0022d1-0406cd",
                    "port_id": "F12",
                    "vlan_id": 70
                },
                {
                    "uri": "/mac-table/e24054-237809",
                    "mac_address": "e24054-237809",
                    "port_id": "Trk9",
                    "vlan_id": 3056
                },
                {
                    "uri": "/mac-table/08000f-ce16be",
                    "mac_address": "08000f-ce16be",
                    "port_id": "D14",
                    "vlan_id": 520
                },
                {
                    "uri": "/mac-table/803f5d-0296da",
                    "mac_address": "803f5d-0296da",
                    "port_id": "Trk7",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/08000f-cb9708",
                    "mac_address": "08000f-cb9708",
                    "port_id": "C20",
                    "vlan_id": 530
                },
                {
                    "uri": "/mac-table/c85acf-9ee0e9",
                    "mac_address": "c85acf-9ee0e9",
                    "port_id": "Trk7",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/d89ef3-42d6ee",
                    "mac_address": "d89ef3-42d6ee",
                    "port_id": "Trk9",
                    "vlan_id": 100
                },
                {
                    "uri": "/mac-table/00206b-415236",
                    "mac_address": "00206b-415236",
                    "port_id": "Trk4",
                    "vlan_id": 200
                },
                {
                    "uri": "/mac-table/001a8c-f0ca41",
                    "mac_address": "001a8c-f0ca41",
                    "port_id": "A6",
                    "vlan_id": 3902
                },
                {
                    "uri": "/mac-table/08000f-cb9c4c",
                    "mac_address": "08000f-cb9c4c",
                    "port_id": "A18",
                    "vlan_id": 520
                }
            ]
        }';

        $start = microtime(true);
        $data = json_decode($data_raw, true);
        foreach($data['mac_table_entry_element'] as $key => $value) {
            $mac = str_replace("-", "", $value['mac_address']);
            $macs[$mac] = [];
            $macs[$mac]['port'] = $value['port_id'];
            $macs[$mac]['vlan'] = $value['vlan_id'];
        }
        
        $uri = "https://bms-srv.doepke.local:2345/bConnect/v1.1/Endpoints.json";
        $baramundi = Http::withoutVerifying()->withBasicAuth('doepke.local\fischer.nils', '97/=h%7zQ%T')->get($uri);
        $i = 1;

        foreach($macs as $key => $info) {
            foreach($baramundi->json() as $endpoint) {
                if(isset($endpoint['MACList'])) {
                    $list = strtolower(str_replace(":", "", $endpoint['MACList']));

                    if(strpos($list, $key) !== false) {
                        $ip = (isset($endpoint['PrimaryIP'])) ? $endpoint['PrimaryIP'] : "N/A";
                        echo $endpoint['HostName']." | " . $ip . " | " . $key . " | " .$info['port'] . " | " . $info['vlan'] . "</br>";
                        
                        $i++;
                        break;
                    }
                }
            }
        }
        $time_elapsed_secs = microtime(true) - $start;
        echo $time_elapsed_secs."sec</br>";
        echo $i;

        return;
        $keys = Key::all();

        $users = User::all();

        $keys2 = "";
        $i = 0;

        foreach($keys as $key) {
            $format_key = explode(" ", EncryptionController::decrypt($key->key));

            $desc = (isset($format_key[2])) ? $format_key[2] : "Imported";
            $correct = $desc. " " . $format_key[0] . " " . $format_key[1];
            $keys2 .= $correct . "\n";

            $i++;
        }

        foreach($users as $user) {

            if($user->privatekey !== NULL and !empty($user->privatekey)) {
                $format_key = explode(" ", EncryptionController::decrypt($user->privatekey));

                if($format_key !== NULL and !empty($format_key)) {  
                    $desc = (isset($format_key[2])) ? $format_key[2] : "Imported";
                    $correct = $desc. " " . $format_key[0] . " " . $format_key[1];
                    $keys2 .= $correct . "\n";

                    $i++;
                }
            }
        }

        return $keys2;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreKeyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
            'key' => 'required|string||starts_with:ssh-rsa|min:50',
        ])->validate();

        $store_key = EncryptionController::encrypt($request->input('key'));

        $key = new Key();
        $key->description = $request->description;
        $key->key = $store_key;
        $key->save();
        LogController::log('Pubkey erstellt', '{"description": "' . $key->description . '"}');

        return redirect()->back()->with('success', 'Key created successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Key  $key
     * @return \Illuminate\Http\Response
     */
    public function show(Key $key)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Key  $key
     * @return \Illuminate\Http\Response
     */
    public function edit(Key $key)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateKeyRequest  $request
     * @param  \App\Models\Key  $key
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKeyRequest $request, Key $key)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Key  $key
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $key)
    {
        $key = Key::find($key->input('id'));
        if($key) {
            $key->delete();
            LogController::log('Pubkey gelöscht', '{"description": "' . $key->description . '"}');
            return redirect()->back()->with('success', 'Key deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Key not found!');
        }

    }
}
