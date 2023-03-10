<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Http\Request;

use App\Anime;
class AnimeController extends BaseController
{
    public function __construct() {
        $this->middleware('auth:api');
        $this->middleware('can:add animes', ['only' => ['store']]);
        $this->middleware('can:edit animes', ['only' => ['update']]);
        $this->middleware('can:delete animes', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Test Method
        // $animes = Anime::all();
        $animes = Anime::orderBy('aired_from', 'ASC')->paginate(10);
        return $this->sendREsponse($animes->toArray(), "Anime sent successfully");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response anime id
     */
    public function store(Request $request)
    {
        // Validate that every single information is correct
        $validatedData = $this->validate_anime_data($request);
        // return $validatedData;
        
        $message = "Anime already exists";
        
        // Save Anime Details in the database and get ID
        // '&' in use() to modify the variable from inside the function
        $anime = Anime::where("mal_id", "=", $validatedData["mal_id"])->first();
        
        if ( $anime == null ) {
            $anime = Anime::withTrashed()->where("mal_id", "=", $validatedData["mal_id"])->first();
            if ( $anime != null ) {
                $anime->restore();
                $message = "Anime restored succesfully";
            } else {
                $message = "Inserted anime succesfully";
                
                // If anime doesn't exist, Insert new one
                $anime = new Anime;
                $anime->mal_id              = (in_array("mal_id", array_keys($validatedData))) ? $validatedData["mal_id"] : null;
                $anime->title               = (in_array("title", array_keys($validatedData))) ? $validatedData['title'] : null;
                $anime->arabic_synopsis     = (in_array("arabic_synopsis", array_keys($validatedData))) ? $validatedData["arabic_synopsis"] : null;
                $anime->anime_type          = (in_array("anime_type", array_keys($validatedData))) ? $validatedData["anime_type"] : null;
                $anime->aired_from          = (in_array("aired_from", array_keys($validatedData))) ? $validatedData["aired_from"] : null;
                $anime->aired_to            = (in_array("aired_to", array_keys($validatedData))) ? $validatedData["aired_to"] : null;
                $anime->background          = (in_array("background", array_keys($validatedData))) ? $validatedData["background"] : null;
                $anime->broadcast           = (in_array("broadcast", array_keys($validatedData))) ? $validatedData["broadcast"] : null;
                $anime->cover_url           = (in_array("cover_url", array_keys($validatedData))) ? $validatedData["cover_url"] : null;
                $anime->duration            = (in_array("duration", array_keys($validatedData))) ? $validatedData["duration"] : null;
                $anime->ending_themes       = (in_array("ending_themes", array_keys($validatedData))) ? json_encode($validatedData["ending_themes"]) : null;
                $anime->episodes            = (in_array("episodes", array_keys($validatedData))) ? $validatedData["episodes"] : null;
                $anime->favorites           = (in_array("favorites", array_keys($validatedData))) ? $validatedData["favorites"] : null;
                $anime->genres              = (in_array("genres", array_keys($validatedData))) ? json_encode($validatedData["genres"]) : null;
                $anime->image_url           = (in_array("image_url", array_keys($validatedData))) ? $validatedData["image_url"] : null;
                $anime->members             = (in_array("members", array_keys($validatedData))) ? $validatedData["members"] : null;
                $anime->opening_themes      = (in_array("opening_themes", array_keys($validatedData))) ? json_encode($validatedData["opening_themes"]) : null;
                $anime->popularity          = (in_array("popularity", array_keys($validatedData))) ? $validatedData["popularity"] : null;
                $anime->rank                = (in_array("rank", array_keys($validatedData))) ? $validatedData["rank"] : null;
                $anime->rating              = (in_array("rating", array_keys($validatedData))) ? $validatedData["rating"] : null;
                $anime->source              = (in_array("source", array_keys($validatedData))) ? $validatedData["source"] : null;
                $anime->status              = (in_array("status", array_keys($validatedData))) ? $validatedData["status"] : null;
                $anime->score           = (in_array("score", array_keys($validatedData))) ? $validatedData["score"] : null;
                $anime->scored_by           = (in_array("scored_by", array_keys($validatedData))) ? $validatedData["scored_by"] : null;
                $anime->synopsis            = (in_array("synopsis", array_keys($validatedData))) ? $validatedData["synopsis"] : null;
                $anime->title_english       = (in_array("title_english", array_keys($validatedData))) ? $validatedData["title_english"] : null;
                $anime->title_japanese      = (in_array("title_japanese", array_keys($validatedData))) ? $validatedData["title_japanese"] : null;
                $anime->title_synonyms      = (in_array("title_synonyms", array_keys($validatedData))) ? $validatedData["title_synonyms"] : null;
                $anime->trailer_url         = (in_array("trailer_url", array_keys($validatedData))) ? $validatedData["trailer_url"] : null;
                $anime->url                 = (in_array("url", array_keys($validatedData))) ? $validatedData["url"] : null;
                $anime->arabic_synopsis                 = (in_array("arabic_synopsis", array_keys($validatedData))) ? $validatedData["arabic_synopsis"] : null;
                $anime->save();
                // return $validatedData;
            }
        }
        
        return $this->sendResponse(["anime_id" => $anime->id, "message" => $message]);
    }

    /**
     * Validate all columns of an anime before inserting it
     *
     * Process all arrays like tags, opening themes
     * and translate english values into Arabic like anime_type and status
     * Then splitting them and convert them to encoded json code
     * 
     * @param  Request $request
     * @return Array $validatedData
     */
    protected function validate_anime_data(Request $request)
    {
        // Validate All input fields data
        $validatedData = $request->validate([
            "arabic_synopsis"   => "nullable",
            "type"              => "nullable",
            "aired.from"        => "nullable|date",
            "aired.to"          => "nullable|date",
            "background"        => "nullable",
            "broadcast"         => "nullable",
            "cover"             => "image|mimes:jpeg,png,jpg,gif,svg",
            "duration"          => "nullable",
            "ending_themes"     => "nullable",
            "episodes"          => "nullable|int",
            "favorites"         => "nullable|int",
            "genres"            => "nullable",
            "image_url"         => "nullable",
            "mal_id"            => "numeric|int",
            "members"           => "nullable|int",
            "title"             => "required",
            "opening_themes"    => "nullable",
            "popularity"        => "nullable|int",
            "rank"              => "nullable|numeric",
            "rating"            => "nullable",
            "source"            => "nullable",
            "status"            => "nullable",
            "scored_by"         => "nullable|int",
            "synopsis"          => "nullable",
            "title_english"     => "nullable",
            "title_japanese"    => "nullable",
            "title_synonyms"    => "nullable",
            "trailer_url"       => "nullable|url",
            "url"               => "nullable|url"
        ]);
        $validatedData = $request;

        // Translate all genres to Arabic
        // splited by commas (,) and translated into arabic
        if ($validatedData->has('genres')) {
            $genres = [];

            // The words that will be translated
            $translateWords = array(
                'Action'           => '????????',
                'Adventure'        => '??????????????',
                'Cars'             => '????????????',
                'Comedy'           => '???????????? ',
                'Dementia'         => '??????????',
                'Demons'           => '????????????',
                'Drama'            => '??????????',
                'Ecchi'            => '????????',
                'Fantasy'          => '????????????????',
                'Game'             => '??????????',
                'Harem'            => '????????',
                'Hentai'           => '????????????',
                'Historical'       => '????????????',
                'Horror'           => '??????',
                'Josei'            => '????????',
                'Kids'             => '??????????',
                'Magic'            => '??????',
                'Martial Arts'     => '????????',
                'Mecha'            => '????????',
                'Military'         => '??????????',
                'Music'            => '????????????',
                'Mystery'          => '????????',
                'Parody'           => '????????',
                'Police'           => '????????????',
                'Psychological'    => '????????',
                'Romance'          => '??????????????',
                'Samurai'          => '??????????????',
                'School'           => '??????????',
                'Sci-Fi'           => '???????? ????????',
                'Seinen'           => '????????',
                'Shoujo Ai'        => '????????',
                'Shoujo'           => '????????',
                'Shounen Ai'       => '??????????',
                'Shounen'          => '??????????',
                'Slice of Life'    => '?????????? ???? ????????????',
                'Space'            => '????????',
                'Sports'           => '??????????',
                'Super Power'      => '?????? ??????????',
                'Supernatural'     => '???????? ??????????????',
                'Thriller'         => '??????????',
                'Vampire'          => '?????????? ????????',
                'Yaoi'             => '??????',
                'Yuri'             => '????????'
            );

            foreach ($validatedData['genres'] as $genre) {
                $genre = str_replace(
                    array_keys($translateWords), 
                    array_values($translateWords), 
                    $genre['name']
                );
                array_push($genres, $genre);
            }
            
            // Remove duplicate words and translate the string into json code
            $validatedData['genres'] = $genres;
        }

        // Remove duplicated title synonyms
        // splited by commas (,) and translated into arabic
        if ($validatedData->has('title_synonyms')) {
            $validatedData['title_synonyms'] = json_encode($validatedData['title_synonyms']);
        }

        // Translate anime type into Arabic (default: Null)
        if ($validatedData->has('type')) {
            switch (strtolower($validatedData->type)) {
                case "tv":
                    $validatedData["anime_type"] = "??????????";
                    break;
                case "movie":
                    $validatedData["anime_type"] = "????????";
                    break;
                case "ova":
                    $validatedData["anime_type"] = "????????";
                    break;
                case "ona":
                    $validatedData["anime_type"] = "????????";
                    break;
                case "special":
                    $validatedData["anime_type"] = "????????";
                    break;
                case "music":
                    $validatedData["anime_type"] = "????????????";
                    break;
                default:
                    $validatedData["anime_type"] = NULL;
            }
        }

        // Translate status of the anime (default: Null)
        if ($validatedData->has('status')) {
            switch (strtolower($validatedData->status)) {
                case 'currently':
                    $validatedData["status"] = "??????????";
                    break;
                case 'finished airing':
                    $validatedData["status"] = "??????????";
                    break;
                case 'upcoming':
                    $validatedData["status"] = "???? ???????? ??????";
                    break;
                default:
                    $validatedData["status"] = Null;
            }
        }

        // Set the rating (age) of the anime (default: Null)
        if ($validatedData->has('rating')) {
            switch (strtolower($validatedData->rating)) {
                case "unknown":
                    $validatedData["rating"] = "?????? ????????";
                    break;
                case "g - all ages":
                    $validatedData["rating"] = "G - ???? ??????????????";
                    break;
                case "pg - children":
                    $validatedData["rating"] = "PG - ??????????";
                    break;
                case "pg-13 - teens 13 or older":
                    $validatedData["rating"] = "PG-13 - ?????????????? 13+";
                    break;
                case "r - 17+ (violence & profanity)":
                    $validatedData["rating"] = "R - 17+ (?????????? ???????????????? ??????????????)";
                    break;
                case "R+ - Mild Nudity":
                    $validatedData["rating"] = "R+ - Mild Nudity";
                    break;
                default:
                    $validatedData["rating"] = $validatedData["rating"];
            }
        }

        // Translate broadcast
        if ($validatedData->has('broadcast')) {
            // The words that will be translates
            $translateWords = array(
                "Saturdays"     => "???????? ??????????",
                "Sundays"       => "???????? ??????????",
                "Mondays"       => "???????? ??????????????",
                "Tuesdays"      => "???????? ????????????????",
                "Wednesdays"    => "???????? ????????????????",
                "Thursdays"     => "???????? ????????????",
                "Fridays"       => "???????? ????????????",
                "at"            => "????",
                "JST"           => "???????????? ?????????????? ???????????? JST",
                "Unknown"       => "?????? ??????????"
            );

            $validatedData["broadcast"] = str_replace(
                array_keys($translateWords), 
                array_values($translateWords), 
                $validatedData->broadcast
            );
        }

        if ($validatedData->has('premiered')) {
            // The words that will be translates
            $translateWords = array(
                "Summer"     => "??????",
                "Winter"       => "????????",
                "Fall"       => "????????",
                "Spring"      => "????????",
                "Unknown"       => "?????? ??????????"
            );

            $validatedData["premiered"] = str_replace(
                array_keys($translateWords), 
                array_values($translateWords), 
                $validatedData->premiered
            );
        }

        if ($validatedData->has('duration')) {
            // The words that will be translates
            $translateWords = array(
                "per ep"     => "?????? ????????",
                "min"       => "??????????",
                "hour"       => "????????",
                "Unknown"       => "?????? ??????????"
            );

            $validatedData["duration"] = str_replace(
                array_keys($translateWords), 
                array_values($translateWords), 
                $validatedData->duration
            );
        }

        if ($validatedData['background'] == "None") {
            $validatedData["background"] = Null;
        }

        if ($validatedData->has('arabic_synopsis')) {
            $validatedData["arabic_synopsis"] = $validatedData->arabic_synopsis;
        }
        

        // Split OSTs into arrays and remove duplicated ones
        // splited by new line (\r\n, \r, \n)
        if ($validatedData->has('opening_themes')) {
            $validatedData->opening_themes = json_encode($validatedData['opening_themes']);
        }
        if ($validatedData->has('ending_themes')) {
            $validatedData->ending_themes = json_encode($validatedData['ending_themes']);
        }

        if ($validatedData->has('cover')) {
            // The name of the image will be { Current_time.extension }
            // e.g. 1589633148.webp
            $cover = time().'.'.validatedData()->cover->getClientOriginalExtension();

            // Move the image to images directory to accessable from web
            validatedData()->cover->move(public_path('images'), $cover);

            // The path of the uploaded image
            $validatedData->cover = cdn('images/'.$cover);
        }

        $validatedData["aired_from"] = new \DateTime($validatedData["aired.from"]);
        $validatedData["aired_string"] = $validatedData["aired.string"];
        $validatedData["aired_to"] = new \DateTime($validatedData["aired.to"]);
        
        unset($validatedData["aired"]);
        unset($validatedData["request_hash"]);
        unset($validatedData["request_cached"]);
        unset($validatedData["request_cache_expiry"]);
        unset($validatedData["jikan_url"]);
        unset($validatedData["headers"]);
        unset($validatedData["type"]);
        return $validatedData->toArray();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
