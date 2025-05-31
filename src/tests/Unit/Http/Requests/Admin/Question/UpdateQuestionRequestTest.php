<?php

namespace Tests\Unit\Http\Requests\Admin\Question;

use App\Http\Requests\Admin\Question\UpdateQuestionRequest;
use App\Models\User;
use App\Models\Question; // For potential route model binding simulation if needed later
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

class UpdateQuestionRequestTest extends TestCase
{
    use RefreshDatabase;

    private UpdateQuestionRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateQuestionRequest();
    }

    #[Test]
    public function it_is_authorized_for_authorized_users(): void
    {
        $authorizedRoles = ['manager', 'corrector', 'general'];
        foreach ($authorizedRoles as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user, 'admin');
            $this->request->setUserResolver(fn() => $user);
            $this->assertTrue($this->request->authorize(), "User with role '$role' should be authorized.");
        }

        $unauthorizedUser = User::factory()->create(['role' => 'user']); // Example of a non-authorized role
        $this->actingAs($unauthorizedUser, 'admin');
        $this->request->setUserResolver(fn() => $unauthorizedUser);
        $this->assertFalse($this->request->authorize(), 'User with role \'user\' should not be authorized.');
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
                    if (!is_array($value)) {
                        return;
                    }
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
            'status' => ['sometimes', 'required', 'string', 'in:pending,approved,rejected'],
            'rejection_reason' => ['required_if:status,rejected', 'nullable', 'string', 'min:10'],
        ];
        // The closure in 'choices' makes direct comparison tricky.
        // We'll compare keys and then individual rules, handling the closure separately.
        $actualRules = $this->request->rules();
        $this->assertEquals(array_keys($expectedRules), array_keys($actualRules));
        foreach ($expectedRules as $field => $rules) {
            if ($field === 'choices') {
                $this->assertEquals($rules[0], $actualRules[$field][0]); // required
                $this->assertEquals($rules[1], $actualRules[$field][1]); // array
                $this->assertEquals($rules[2], $actualRules[$field][2]); // size:4
                $this->assertInstanceOf(\Closure::class, $actualRules[$field][3]); // closure
            } else {
                $this->assertEquals($rules, $actualRules[$field], "Rules for field '$field' do not match.");
            }
        }
    }

    #[Test]
    #[DataProvider('validationDataset')]
    public function validation_passes_or_fails_as_expected(array|callable $data, bool $passes, array $expectedErrors = []): void
    {
        // If the request involves route model binding, you might need to mock the route and its parameters.
        // For UpdateQuestionRequest, it might expect a 'question' parameter in the route for some rules (e.g. unique-except-self).
        // if ($this->request->route('question') === null) {
        //     $question = Question::factory()->create(); // Ensure a question exists for context
        //     $this->request->setRouteResolver(function () use ($question) {
        //         $route = new \Illuminate\Routing\Route(['PUT', 'PATCH'], 'admin/questions/{question}', []);
        //         $route->bind(new \Illuminate\Http\Request());
        //         $route->setParameter('question', $question);
        //         return $route;
        //     });
        // }

        if (is_callable($data)) {
            $data = $data();
        }

        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());
        $this->assertEquals($passes, $validator->passes(), json_encode($validator->errors()->all()));
        if (!$passes) {
            $this->assertEquals($expectedErrors, $validator->errors()->messages());
        }
    }
    
    public static function validationDataset(): array
    {
        $baseValidData = [
            'question_text' => 'This is a valid question text with enough characters.',
            'choices' => ['Unique Choice A', 'Unique Choice B', 'Unique Choice C', 'Unique Choice D'],
            'correct_choice' => 1,
            'explanation' => 'This is a valid explanation, also with enough characters.',
            'difficulty_level' => 'medium',
            'category' => 'General Knowledge',
        ];

        return [
            'passes_with_all_valid_data' => [
                'data' => $baseValidData,
                'passes' => true,
            ],
            'fails_question_text_required' => [
                'data' => array_merge($baseValidData, ['question_text' => '']),
                'passes' => false,
                'expectedErrors' => ['question_text' => ['The question text is required.']]
            ],
            'fails_question_text_min' => [
                'data' => array_merge($baseValidData, ['question_text' => 'Short']),
                'passes' => false,
                'expectedErrors' => ['question_text' => ['The question text must be at least 10 characters.']]
            ],
            'fails_choices_required' => [
                'data' => collect($baseValidData)->except('choices')->all(),
                'passes' => false,
                'expectedErrors' => ['choices' => ['You must provide 4 choices for the question.']]
            ],
            'fails_choices_not_array' => [
                'data' => array_merge($baseValidData, ['choices' => 'not-an-array']),
                'passes' => false,
                'expectedErrors' => ['choices' => ['The choices field must be an array.', 'You must provide exactly 4 choices for the question.']]
            ],
            'fails_choices_size' => [
                'data' => array_merge($baseValidData, ['choices' => ['A', 'B', 'C']]),
                'passes' => false,
                'expectedErrors' => [
                    'choices' => ['You must provide exactly 4 choices for the question.'],
                    'choices.0' => ['Each choice must be at least 2 characters.'],
                    'choices.1' => ['Each choice must be at least 2 characters.'],
                    'choices.2' => ['Each choice must be at least 2 characters.']
                ]
            ],
            'fails_choices_not_unique' => [
                'data' => array_merge($baseValidData, ['choices' => ['A', 'A', 'B', 'C']]),
                'passes' => false,
                'expectedErrors' => [
                    'choices' => ['All choices must be unique.'],
                    'choices.0' => ['Each choice must be at least 2 characters.'],
                    'choices.1' => ['Each choice must be at least 2 characters.'],
                    'choices.2' => ['Each choice must be at least 2 characters.'],
                    'choices.3' => ['Each choice must be at least 2 characters.']
                ]
            ],
            'fails_choices_item_min' => [
                'data' => array_merge($baseValidData, ['choices' => ['Ok', 'Ok', 'Ok', 'A']]),
                'passes' => false,
                'expectedErrors' => [
                    'choices' => ['All choices must be unique.'],
                    'choices.3' => ['Each choice must be at least 2 characters.']
                ]
            ],
            'passes_explanation_nullable' => [
                'data' => array_merge($baseValidData, ['explanation' => null]),
                'passes' => true,
            ],
            'fails_explanation_min' => [
                'data' => array_merge($baseValidData, ['explanation' => 'Short']),
                'passes' => false,
                'expectedErrors' => ['explanation' => ['The explanation must be at least 10 characters if provided.']]
            ],
            'fails_difficulty_level_invalid' => [
                'data' => array_merge($baseValidData, ['difficulty_level' => 'invalid']),
                'passes' => false,
                'expectedErrors' => ['difficulty_level' => ['The selected difficulty level is invalid.']]
            ],
            'passes_status_pending' => [
                'data' => array_merge($baseValidData, ['status' => 'pending']),
                'passes' => true,
            ],
            'fails_status_invalid' => [
                'data' => array_merge($baseValidData, ['status' => 'invalid_status']),
                'passes' => false,
                'expectedErrors' => ['status' => ['The selected status is invalid.']]
            ],
            'passes_status_rejected_with_reason' => [
                'data' => array_merge($baseValidData, ['status' => 'rejected', 'rejection_reason' => 'This is a valid rejection reason.']),
                'passes' => true,
            ],
            'fails_status_rejected_reason_required' => [
                'data' => array_merge($baseValidData, ['status' => 'rejected', 'rejection_reason' => '']),
                'passes' => false,
                'expectedErrors' => ['rejection_reason' => ['Please provide a reason for rejection.']]
            ],
            'fails_status_rejected_reason_min' => [
                'data' => array_merge($baseValidData, ['status' => 'rejected', 'rejection_reason' => 'Short']),
                'passes' => false,
                'expectedErrors' => ['rejection_reason' => ['The rejection reason must be at least 10 characters.']]
            ],
             'passes_status_approved_reason_not_needed' => [
                'data' => array_merge($baseValidData, ['status' => 'approved', 'rejection_reason' => null]),
                'passes' => true,
            ],
            'choices_fail_extreme_not_unique' => [
                'data' => [
                    'question_text' => 'What is the capital of France?',
                    'choices' => ['Paris', 'Paris', 'Paris', 'Paris'], // Not unique
                    'correct_choice' => 1,
                    'difficulty_level' => 'easy',
                    'status' => 'pending',
                ],
                'passes' => false,
                'expectedErrors' => ['choices' => ['All choices must be unique.']] 
            ],
            'choices_fail_extreme_not_size_4' => [
                'data' => [
                    'question_text' => 'What is the capital of France?',
                    'choices' => ['Paris', 'London', 'Rome'], // Size 3
                    'correct_choice' => 1,
                    'difficulty_level' => 'easy',
                    'status' => 'pending',
                ],
                'passes' => false,
                'expectedErrors' => ['choices' => ['You must provide exactly 4 choices for the question.']]
            ],
        ];
    }
} 