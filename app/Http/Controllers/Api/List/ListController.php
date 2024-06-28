<?php

namespace App\Http\Controllers\Api\List;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Questions;
use App\Models\Socials;
use OpenApi\Annotations as OA;

class ListController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/socials",
     *     summary="Get a list of socials",
     *     tags={"List"},
     *     @OA\Response(response="200", description="List of socials"),
     * )
     */
    public function socials(Request $request)
    {
        $socials = Socials::select('id', 'name', 'icon', 'path')->get();

        $items = $socials->map(function ($social) {
            return [
                'id' => $social->id,
                'name' => $social->name,
                'path' => $social->path,
                'icon' => $social->icon_url,
            ];
        });

        return response()->json(['socials' => $items], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/questions",
     *     summary="Get a list of questions with their answers",
     *     tags={"List"},
     *     @OA\Response(response="200", description="List of questions with answers"),
     * )
     */
    public function questions(Request $request)
    {
        $questions = Questions::with('answer_id, question_id, name_uz, name_ru, name_en')->get(['id', 'name_uz, name_ru, name_en']);

        $items = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name_uz' => $question->name_uz,
                'name_ru' => $question->name_ru,
                'name_en' => $question->name_en,
                'answers' => $question->answers->map(function ($answer) {
                    return [
                        'id' => $answer->id,
                        'name_uz' => $answer->name_uz,
                        'name_ru' => $answer->name_ru,
                        'name_en' => $answer->name_en,
                    ];
                }),
            ];
        });

        return response()->json(['questions' => $items], 200);
    }
}
