<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Series;
use App\Content;
use App\Author;

class ApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function getCid($id)
    {
        return "TSBA_BW".str_pad($id, 14, "0", STR_PAD_LEFT)."_58";
    }

    private function getThumb($id)
    {
        $cid = getCid($id);
        return base64_decode("aHR0cHM6Ly9pMC53cC5jb20vc3RvcmUtdHNicC0wMDEuaGVyb2t1LmNvbS5zMy5hbWF6b25hd3MuY29tL3Byb2R1Y3Rpb24vZGVsaXZlcnk=")."/$cid/{$cid}_cover.jpg";
    }

    public function stats(Request $request)
    {
        return response()->json([
            'contents' => [
                'count' => Content::count(),
                'type' => [
                    'novel' => Content::where('type', '1')->count(),
                    'comic' => Content::where('type', '2')->count(),
                ],
                'format' => [
                    'reflowable' => Content::where('format', '0')->count(),
                    'fixedlayout' => Content::where('format', '1')->count(),
                    'omf' => Content::where('format', '2')->count(),
                ]
            ],
            'authors' => [
                'count' => Author::count()
            ],
            'series' => [
                'count' => Series::count()
            ]
        ]);
    }

    /**
     * Get various attribute counts for a query
     * @param Request $request
     *
     */
    public function count(Request $request)
    {

    }

    /**
     * Get paginated results for a query
     * @param Request $request
     */
    public function search(Request $request)
    {

    }

    public function contents(Request $request)
    {
        return Content::paginate(25);
    }

    public function upsert(Request $request, $cid)
    {
        $contentAttrs = [
            'id' => $cid,
            "isbn" => $request->get("isbn"),
            "name" => $request->get("name"),
            "name2" => $request->get("name2"),
            "description" => $request->get("description"),
            "description2" => $request->get("description2"),
            "type" => $request->get("type"),
            "format" => $request->get("format"),
        ];
        $item = Content::firstOrNew(
            ['id' => $cid], $contentAttrs
        );

        $author = Author::firstOrNew(
            ['name' => $request->get("author"), 'alt' => $request->get("author2")]
        );
        $author->save();
        $item->author()->associate($author);

        $series = Series::firstOrNew(
            ['name' => $request->get("series")]
        );
        $series->save();
        $item->series()->associate($series);


        // Only thing that should change when updating
        $item->updated_at = Carbon::createFromFormat("Y-m-d\TH:i:sP", $request->get("updated_at"));
        $item->deliver_at = Carbon::createFromFormat("Y-m-d\TH:i:sP", $request->get("deliver_at"));

        $item->save();

        return $item;
    }

    //
}
