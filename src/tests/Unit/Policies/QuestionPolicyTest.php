<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Models\Question;
use App\Policies\QuestionPolicy;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;  
use Illuminate\Auth\Access\Response;

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

    private function assertPolicyAllows($result)
    {
        if ($result instanceof Response) {
            $this->assertTrue($result->allowed());
        } else {
            $this->assertTrue($result);
        }
    }

    private function assertPolicyDenies($result)
    {
        if ($result instanceof Response) {
            $this->assertFalse($result->allowed());
        } else {
            $this->assertFalse($result);
        }
    }

    #[Test]
    public function manager_can_do_everything()
    {
        $manager = $this->makeUser('manager', 1);
        $question = $this->makeQuestion(2, 'pending');
        $this->assertPolicyAllows($this->policy->viewAny($manager));
        $this->assertPolicyAllows($this->policy->view($manager, $question));
        $this->assertPolicyAllows($this->policy->create($manager));
        $this->assertPolicyAllows($this->policy->update($manager, $question));
        $this->assertPolicyAllows($this->policy->delete($manager, $question));
        $this->assertPolicyAllows($this->policy->approve($manager, $question));
    }

    #[Test]
    public function corrector_can_view_update_approve_but_not_delete()
    {
        $corrector = $this->makeUser('corrector', 2);
        $question = $this->makeQuestion(3, 'pending');
        $this->assertPolicyAllows($this->policy->viewAny($corrector));
        $this->assertPolicyAllows($this->policy->view($corrector, $question));
        $this->assertPolicyDenies($this->policy->create($corrector));
        $this->assertPolicyAllows($this->policy->update($corrector, $question));
        $this->assertPolicyDenies($this->policy->delete($corrector, $question));
        $this->assertPolicyAllows($this->policy->approve($corrector, $question));
    }

    #[Test]
    public function general_can_view_and_create()
    {
        $general = $this->makeUser('general', 3);
        $ownQuestion = $this->makeQuestion(3, 'pending');
        $otherQuestion = $this->makeQuestion(4, 'pending');

        $this->assertPolicyAllows($this->policy->viewAny($general));
        $this->assertPolicyAllows($this->policy->view($general, $ownQuestion));
        $this->assertPolicyDenies($this->policy->view($general, $otherQuestion));
        $this->assertPolicyAllows($this->policy->create($general));
    }

    #[Test]
    public function general_can_update_own_pending_question()
    {
        $general = $this->makeUser('general', 3);
        $ownPending = $this->makeQuestion(3, 'pending');
        $ownRejected = $this->makeQuestion(3, 'rejected');
        $ownApproved = $this->makeQuestion(3, 'approved');
        $othersPending = $this->makeQuestion(4, 'pending');
        $this->assertPolicyAllows($this->policy->update($general, $ownPending));
        $this->assertPolicyAllows($this->policy->update($general, $ownRejected));
        $this->assertPolicyDenies($this->policy->update($general, $ownApproved));
        $this->assertPolicyDenies($this->policy->update($general, $othersPending));
    }

    #[Test]
    public function general_cannot_delete_approve_restore_forceDelete()
    {
        $general = $this->makeUser('general', 3);
        $question = $this->makeQuestion(3, 'pending');
        $this->assertPolicyDenies($this->policy->delete($general, $question));
        $this->assertPolicyDenies($this->policy->approve($general, $question));
    }
} 