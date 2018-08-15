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
     * @return any Paginated search results
     */
    public function search(Request $request)
    {
        $data = $request->json()->all();
        $query = Content::where(function($q) use ($request, $data) {
            $term = $data['query'];
            $q->orWhere('name', 'like', "%{$term}%");
            $q->orWhere('name2', 'like', "%{$term}%");
            $q->orWhere('description', 'like', "%{$term}%");
            $q->orWhere('description2', 'like', "%{$term}%");
        });
        if (array_key_exists('format', $data))
            if (is_array($data['format']))
                $query->where(function ($encapsulated) use ($data) {
                    for ($i = 0; $i <= 2; ++$i) { // Each type
                        if(in_array($i, $data['format']))
                            $encapsulated->orWhere('format', $i);
                        else
                            $encapsulated->where('format', '!=', $i);
                    }
                });
        if (array_key_exists('type', $data))
            if (is_array($data['type']))
                $query->where(function ($encapsulated) use ($data) {
                    for ($i = 1; $i <= 2; ++$i) { // Each type
                        if(in_array($i, $data['type']))
                            $encapsulated->orWhere('type', $i);
                        else
                            $encapsulated->where('type', '!=', $i);
                    }
                });
        if (array_key_exists('sort', $data) && array_key_exists('by', $data))
            switch ($data['sort']) {
                case 1:
                    {
                        $query->orderBy($data['by'], 'desc');
                        break;
                    }
                case 2:
                    {
                        $query->orderBy($data['by'], 'asc');
                        break;
                    }
            }
        //return dd($query->toSql());
        return $query->with('author', 'series')->paginate(25);
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
