<?php

namespace Tests\Unit\Http\Requests\Admin\Question;

use App\Http\Requests\Admin\Question\StoreQuestionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class StoreQuestionRequestTest extends TestCase
{
    use RefreshDatabase;

    private StoreQuestionRequest $rules;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rules = new StoreQuestionRequest();
    }

    #[Test]
    public function it_is_authorized_for_authorized_users(): void
    {
        $managerUser = User::factory()->create(['role' => 'manager']);
        $this->actingAs($managerUser, 'admin');
        $this->rules->setUserResolver(fn() => $managerUser);
        $this->assertTrue($this->rules->authorize(), 'Manager should be authorized.');

        $generalUser = User::factory()->create(['role' => 'general']);
        $this->actingAs($generalUser, 'admin');
        $this->rules->setUserResolver(fn() => $generalUser);
        $this->assertTrue($this->rules->authorize(), 'General role user should be authorized.');

        $otherUser = User::factory()->create(['role' => 'corrector']);
        $this->actingAs($otherUser, 'admin');
        $this->rules->setUserResolver(fn() => $otherUser);
        $this->assertFalse($this->rules->authorize(), 'Corrector should not be authorized.');
    }

    #[Test]
    public function it_has_the_correct_rules(): void
    {
        $expectedRules = [
            'question_text' => ['required', 'string', 'min:10'],
            'choices' => [
                'required',
                'array',
                'size:4',
                function ($attribute, $value, $fail) {
                    if (count(array_unique($value)) !== count($value)) {
                        $fail('All choices must be unique.');
                    }
                },
            ],
            'choices.*' => ['required', 'string', 'min:2', 'max:255'],
            'correct_choice' => ['required', 'integer', 'min:1', 'max:4'],
            'explanation' => ['nullable', 'string', 'min:10'],
            'difficulty_level' => ['required', 'string', 'in:easy,medium,hard'],
            'category' => ['nullable', 'string', 'max:255'],
        ];
        $this->assertEquals($expectedRules, $this->rules->rules());
    }

    #[Test]
    #[DataProvider('validationProvider')]
    public function it_validates_inputs_correctly(array $data, bool $passes, array $errors = []): void
    {
        $validator = Validator::make($data, $this->rules->rules(), $this->rules->messages());
        $this->assertEquals($passes, $validator->passes(), json_encode($validator->errors()->all()));
        if (!$passes) {
            $this->assertEquals($errors, $validator->errors()->messages());
        }
    }
    
    public static function validationProvider(): array
    {
        $commonChoicesData = [
            'question_text' => 'This is a valid question text for choices tests.',
            'correct_choice' => 1,
            'difficulty_level' => 'easy',
            // 'explanation' and 'category' can be omitted as they are nullable
        ];

        return [
            'request_should_fail_when_question_text_is_missing' => [
                'data' => [/* no q_text */ 'choices' => ['c1', 'c2', 'c3', 'c4'], 'correct_choice' => 1, 'difficulty_level' => 'easy'],
                'passes' => false,
                'errors' => ['question_text' => ['The question text is required.']]
            ],
            'request_should_fail_when_question_text_is_too_short' => [
                'data' => ['question_text' => 'Short', 'choices' => ['c1','c2','c3','c4'], 'correct_choice' => 1, 'difficulty_level' => 'easy'],
                'passes' => false,
                'errors' => ['question_text' => ['The question text must be at least 10 characters.']]
            ],

            // Choices specific tests
            'choices_fail_when_not_an_array' => [
                'data' => array_merge($commonChoicesData, ['choices' => 'not-an-array']),
                'passes' => false,
                'errors' => [
                    'choices' => [
                        'The choices field must be an array.',
                        'You must provide exactly 4 choices for the question.'
                    ]
                ]
            ],
            'choices_fail_when_not_size_4' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['Paris', 'London', 'Rome']]), // 3 items
                'passes' => false,
                'errors' => ['choices' => ['You must provide exactly 4 choices for the question.']] // Custom message
            ],
            'choices_fail_when_not_unique' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['Paris', 'Paris', 'Berlin', 'Madrid']]), // Paris duplicated
                'passes' => false,
                'errors' => ['choices' => ['All choices must be unique.']] // From closure
            ],
            'choices_fail_when_item_is_too_short' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['A', 'Berlin', 'Madrid', 'Rome']]), // 'A' is too short
                'passes' => false,
                'errors' => ['choices.0' => ['Each choice must be at least 2 characters.']] // Custom message for choices.*
            ],
            'choices_fail_when_item_is_not_a_string' => [
                'data' => array_merge($commonChoicesData, ['choices' => [123, 'Berlin', 'Madrid', 'Rome']]), // 123 is not a string
                'passes' => false,
                'errors' => ['choices.0' => ['The choices.0 field must be a string.']] // Corrected expected error
            ],

            'request_should_fail_when_correct_choice_is_not_in_range' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['c1','c2','c3','c4'], 'correct_choice' => 5]),
                'passes' => false,
                'errors' => ['correct_choice' => ['The correct choice must be between 1 and 4.']]
            ],
            'request_should_pass_with_valid_data_all_fields' => [
                'data' => [
                    'question_text' => 'What is the official currency of Japan?',
                    'choices' => ['Yuan', 'Yen', 'Won', 'Dollar'],
                    'correct_choice' => 2,
                    'explanation' => 'The Japanese Yen is the official currency.',
                    'difficulty_level' => 'medium',
                    'category' => 'Geography',
                ],
                'passes' => true,
            ],
            'request_should_pass_with_valid_data_nullable_fields_empty' => [
                'data' => [
                    'question_text' => 'Which planet is known as the Red Planet?',
                    'choices' => ['Earth', 'Mars', 'Jupiter', 'Saturn'],
                    'correct_choice' => 2,
                    'explanation' => null,
                    'difficulty_level' => 'easy',
                    'category' => null,
                ],
                'passes' => true,
            ],
            'request_should_fail_when_explanation_is_too_short' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['c1','c2','c3','c4'], 'explanation' => 'Short']),
                'passes' => false,
                'errors' => ['explanation' => ['The explanation must be at least 10 characters if provided.']]
            ],
            'request_should_fail_when_difficulty_level_is_invalid' => [
                'data' => array_merge($commonChoicesData, ['choices' => ['c1','c2','c3','c4'], 'difficulty_level' => 'extreme']),
                'passes' => false,
                'errors' => ['difficulty_level' => ['The selected difficulty level is invalid.']]
            ],
        ];
    }
} 