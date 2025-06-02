<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use InvalidArgumentException;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    protected $question;
    protected $creator;
    protected $approver;
    protected $rejecter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creator = User::factory()->create(['role' => 'general']);
        $this->approver = User::factory()->create(['role' => 'corrector']);
        $this->rejecter = User::factory()->create(['role' => 'corrector']);

        // Create a question that is clearly in a 'rejected' state
        $this->question = Question::factory()->create([
            'created_by' => $this->creator->id,
            'status' => 'rejected',
            'rejected_by' => $this->rejecter->id,
            'rejection_reason' => 'Initial test rejection reason',
            'rejected_at' => now(),
            'approved_by' => null,
            'approved_at' => null
        ]);
    }

    #[Test]
    public function question_has_creator_relationship(): void
    {
        $this->assertInstanceOf(User::class, $this->question->creator);
        $this->assertEquals($this->creator->id, $this->question->creator->id);
    }

    #[Test]
    public function question_has_approver_relationship(): void
    {
        $approver = User::factory()->create();
        $question = Question::factory()->create([
            'approved_by' => $approver->id,
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->assertInstanceOf(User::class, $question->approver);
        $this->assertEquals($approver->id, $question->approver->id);
    }

    #[Test]
    public function question_has_rejecter_relationship(): void
    {
        $rejecter = User::factory()->create();
        $question = Question::factory()->create([
            'rejected_by' => $rejecter->id,
        ]);
        
        // Reload question if the relationship is not auto-loaded
        $question->refresh();

        $this->assertInstanceOf(User::class, $question->rejecter);
        $this->assertEquals($rejecter->id, $question->rejecter->id);
    }

    #[Test]
    public function question_scope_pending(): void
    {
        Question::factory()->pending()->count(3)->create();
        
        $pendingQuestions = Question::pending()->get();
        
        $this->assertEquals(3, $pendingQuestions->count());
        $pendingQuestions->each(function ($question) {
            $this->assertEquals('pending', $question->status);
            $this->assertNull($question->approved_by);
            $this->assertNull($question->rejected_by);
        });
    }

    #[Test]
    public function question_scope_approved(): void
    {
        Question::factory()->approved()->count(2)->create();
        
        $approvedQuestions = Question::approved()->get();
        
        $this->assertEquals(2, $approvedQuestions->count());
        $approvedQuestions->each(function ($question) {
            $this->assertEquals('approved', $question->status);
            $this->assertNotNull($question->approved_by);
            $this->assertNotNull($question->approved_at);
        });
    }

    #[Test]
    public function question_scope_rejected(): void
    {
        $rejectedQuestions = Question::rejected()->get();
        
        $this->assertEquals(1, $rejectedQuestions->count());
        $rejectedQuestions->each(function ($question) {
            $this->assertEquals('rejected', $question->status);
            $this->assertNotNull($question->rejected_by);
            $this->assertNotNull($question->rejected_at);
            $this->assertNotNull($question->rejection_reason);
            $this->assertNull($question->approved_by);
            $this->assertNull($question->approved_at);
        });
    }

    #[Test]
    public function question_scope_by_difficulty(): void
    {
        // Ensure a clean state before running this test
        Question::query()->delete(); // Or use truncate if you're not using transactions

        Question::factory()->difficulty('easy')->create();
        Question::factory()->difficulty('medium')->create();
        Question::factory()->difficulty('hard')->create();
        
        $easyQuestions = Question::byDifficulty('easy')->get();
        $this->assertEquals(1, $easyQuestions->count());
        $this->assertEquals('easy', $easyQuestions->first()->difficulty_level);
        
        $mediumQuestions = Question::byDifficulty('medium')->get();
        $this->assertEquals(1, $mediumQuestions->count());
        $this->assertEquals('medium', $mediumQuestions->first()->difficulty_level);
        
        $hardQuestions = Question::byDifficulty('hard')->get();
        $this->assertEquals(1, $hardQuestions->count());
        $this->assertEquals('hard', $hardQuestions->first()->difficulty_level);
    }

    #[Test]
    public function question_scope_by_category(): void
    {
        // Ensure a clean state for this specific test's assertions
        Question::query()->delete();

        Question::factory()->create(['category' => 'Math']);
        Question::factory()->create(['category' => 'Science']);
        
        $mathQuestions = Question::byCategory('Math')->get();
        $this->assertEquals(1, $mathQuestions->count());
        $this->assertEquals('Math', $mathQuestions->first()->category);
        
        $scienceQuestions = Question::byCategory('Science')->get();
        $this->assertEquals(1, $scienceQuestions->count());
        $this->assertEquals('Science', $scienceQuestions->first()->category);
    }

    #[Test]
    public function question_correct_answer_accessor(): void
    {
        $question = Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 2
        ]);
        
        $this->assertEquals('B', $question->correct_answer);
    }

    #[Test]
    public function question_choices_mutator_validates_number_of_choices(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A question must have exactly 4 choices.');
        
        Question::factory()->create([
            'choices' => ['A', 'B', 'C'] // Only 3 choices
        ]);
    }

    #[Test]
    public function question_correct_choice_mutator_validates_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Correct choice must be between 1 and 4.');
        
        Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 5 // Invalid choice number
        ]);
    }

    #[Test]
    public function question_choices_mutator_handles_json_string(): void
    {
        $question = Question::factory()->create([
            'choices' => json_encode(['A', 'B', 'C', 'D']),
            'correct_choice' => 1
        ]);
        
        $this->assertIsArray($question->choices);
        $this->assertCount(4, $question->choices);
        $this->assertEquals(['A', 'B', 'C', 'D'], $question->choices);
    }

    #[Test]
    public function question_soft_deletes(): void
    {
        $question = Question::factory()->create();
        $questionId = $question->id;
        
        $question->delete();
        
        $this->assertSoftDeleted('questions', ['id' => $questionId]);
        $this->assertDatabaseHas('questions', ['id' => $questionId]);
    }

    #[Test]
    public function question_casts_attributes(): void
    {
        $question = Question::factory()->create([
            'choices' => ['A', 'B', 'C', 'D'],
            'correct_choice' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'approved_at' => now(),
            'rejected_at' => now()
        ]);
        
        $this->assertIsArray($question->choices);
        $this->assertIsInt($question->correct_choice);
        $this->assertInstanceOf(\DateTime::class, $question->created_at);
        $this->assertInstanceOf(\DateTime::class, $question->updated_at);
        $this->assertInstanceOf(\DateTime::class, $question->approved_at);
        $this->assertInstanceOf(\DateTime::class, $question->rejected_at);
    }

    #[Test]
    public function question_belongs_to_creator_approver_rejecter(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $question = Question::factory()->create([
            'created_by' => $user1->id,
            'approved_by' => $user2->id,
            'rejected_by' => $user3->id,
        ]);

        $this->assertInstanceOf(User::class, $question->creator);
        $this->assertEquals($user1->id, $question->creator->id);
        $this->assertInstanceOf(User::class, $question->approver);
        $this->assertEquals($user2->id, $question->approver->id);
        $this->assertInstanceOf(User::class, $question->rejecter);
        $this->assertEquals($user3->id, $question->rejecter->id);
    }

    #[Test]
    public function test_status_scopes(): void
    {
        Question::truncate();
        Question::factory()->create(['status' => 'pending']);
        Question::factory()->count(2)->create(['status' => 'approved']);
        
        // Create 3 new rejected questions using the factory state
        Question::factory()->count(3)->rejected()->create();

        $this->assertCount(1, Question::pending()->get());
        $this->assertCount(2, Question::approved()->get());
        $this->assertCount(3, Question::rejected()->get());
    }

    #[Test]
    public function test_by_difficulty_scope(): void
    {
        Question::truncate();
        Question::factory()->create(['difficulty_level' => 'easy']);
        Question::factory()->count(2)->create(['difficulty_level' => 'medium']);

        $this->assertCount(1, Question::byDifficulty('easy')->get());
        $this->assertCount(2, Question::byDifficulty('medium')->get());
    }

    #[Test]
    public function test_by_category_scope(): void
    {
        Question::truncate();
        Question::factory()->create(['category' => 'Math']);
        Question::factory()->count(2)->create(['category' => 'Science']);

        $mathQuestions = Question::byCategory('Math')->get();
        $scienceQuestions = Question::byCategory('Science')->get();

        $this->assertCount(1, $mathQuestions);
        $this->assertEquals('Math', $mathQuestions->first()->category);
    
        $this->assertCount(2, $scienceQuestions);
        $scienceQuestions->each(function ($question) {
            $this->assertEquals('Science', $question->category);
        });
    }

    #[Test]
    public function get_correct_answer_attribute_returns_correct_choice_text(): void
    {
        $choices = ['A', 'B', 'C', 'D'];
        $question = Question::factory()->make([
            'choices' => $choices,
            'correct_choice' => 2 // 'B'
        ]);
        $this->assertEquals('B', $question->correct_answer);

    }

    #[Test]
    public function set_choices_attribute_validates_exactly_four_choices(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A question must have exactly 4 choices.');
        Question::factory()->create(['choices' => ['A', 'B', 'C']]); // Too few
    }

    #[Test]
    public function set_choices_attribute_validates_exactly_four_choices_too_many(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A question must have exactly 4 choices.');
        Question::factory()->create(['choices' => ['A', 'B', 'C', 'D', 'E']]); // Too many
    }

    #[Test]
    public function set_choices_attribute_accepts_json_string(): void
    {
        $choicesArray = ['Opt1', 'Opt2', 'Opt3', 'Opt4'];
        $choicesJson = json_encode($choicesArray);
        $question = Question::factory()->create(['choices' => $choicesJson]);
        $this->assertEquals($choicesArray, $question->choices);
    }
    
    #[Test]
    public function set_choices_attribute_accepts_array(): void
    {
        $choicesArray = ['Choice W', 'Choice X', 'Choice Y', 'Choice Z'];
        $question = Question::factory()->create(['choices' => $choicesArray]);
        $this->assertEquals($choicesArray, $question->choices);
    }

    #[Test]
    public function set_correct_choice_attribute_validates_range_too_low(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Correct choice must be between 1 and 4.');
        Question::factory()->create(['correct_choice' => 0]);
    }

    #[Test]
    public function set_correct_choice_attribute_validates_range_too_high(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Correct choice must be between 1 and 4.');
        Question::factory()->create(['correct_choice' => 5]);
    }

    #[Test]
    public function set_correct_choice_attribute_accepts_valid_range(): void
    {
        $question = Question::factory()->create(['correct_choice' => 3]);
        $this->assertEquals(3, $question->correct_choice);
    }
} 