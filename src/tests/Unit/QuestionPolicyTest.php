<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Question;
use App\Policies\QuestionPolicy;
use PHPUnit\Framework\TestCase;

class QuestionPolicyTest extends TestCase
{
    protected $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new QuestionPolicy();
    }

    private function makeUser($role, $id = 1)
    {
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hasRole'])
            ->getMock();
        $user->method('hasRole')->willReturnCallback(function($roles) use ($role) {
            if (is_array($roles)) return in_array($role, $roles);
            return $role === $roles;
        });
        $user->id = $id;
        $user->role = $role;
        return $user;
    }

    private function makeQuestion($created_by = 1, $status = 'pending')
    {
        $q = new Question();
        $q->created_by = $created_by;
        $q->status = $status;
        return $q;
    }

    public function test_manager_can_do_everything()
    {
        $manager = $this->makeUser('manager', 1);
        $question = $this->makeQuestion(2, 'pending');
        $this->assertTrue($this->policy->viewAny($manager));
        $this->assertTrue($this->policy->view($manager, $question));
        $this->assertTrue($this->policy->create($manager));
        $this->assertTrue($this->policy->update($manager, $question));
        $this->assertTrue($this->policy->delete($manager, $question));
        $this->assertTrue($this->policy->approve($manager, $question));
        $this->assertTrue($this->policy->restore($manager, $question));
        $this->assertTrue($this->policy->forceDelete($manager, $question));
    }

    public function test_corrector_can_view_update_approve_but_not_delete()
    {
        $corrector = $this->makeUser('corrector', 2);
        $question = $this->makeQuestion(3, 'pending');
        $this->assertTrue($this->policy->viewAny($corrector));
        $this->assertTrue($this->policy->view($corrector, $question));
        $this->assertFalse($this->policy->create($corrector));
        $this->assertTrue($this->policy->update($corrector, $question));
        $this->assertFalse($this->policy->delete($corrector, $question));
        $this->assertTrue($this->policy->approve($corrector, $question));
        $this->assertFalse($this->policy->restore($corrector, $question));
        $this->assertFalse($this->policy->forceDelete($corrector, $question));
    }

    public function test_general_can_view_and_create()
    {
        $general = $this->makeUser('general', 3);
        $question = $this->makeQuestion(3, 'pending');
        $this->assertTrue($this->policy->viewAny($general));
        $this->assertTrue($this->policy->view($general, $question));
        $this->assertTrue($this->policy->create($general));
    }

    public function test_general_can_update_own_pending_question()
    {
        $general = $this->makeUser('general', 3);
        $ownPending = $this->makeQuestion(3, 'pending');
        $ownRejected = $this->makeQuestion(3, 'rejected');
        $ownApproved = $this->makeQuestion(3, 'approved');
        $othersPending = $this->makeQuestion(4, 'pending');
        $this->assertTrue($this->policy->update($general, $ownPending));
        $this->assertTrue($this->policy->update($general, $ownRejected));
        $this->assertFalse($this->policy->update($general, $ownApproved));
        $this->assertFalse($this->policy->update($general, $othersPending));
    }

    public function test_general_cannot_delete_approve_restore_forceDelete()
    {
        $general = $this->makeUser('general', 3);
        $question = $this->makeQuestion(3, 'pending');
        $this->assertFalse($this->policy->delete($general, $question));
        $this->assertFalse($this->policy->approve($general, $question));
        $this->assertFalse($this->policy->restore($general, $question));
        $this->assertFalse($this->policy->forceDelete($general, $question));
    }
} 