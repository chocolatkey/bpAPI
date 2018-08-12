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
            "updated_at" => Carbon::createFromFormat("Y-m-d\TH:i:sP", $request->get("updated_at")),
            "deliver_at" => Carbon::createFromFormat("Y-m-d\TH:i:sP", $request->get("deliver_at")),
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

        $item->save();

        return $item;

        //return response()->json(Content::paginate(25));
    }

    //
}
