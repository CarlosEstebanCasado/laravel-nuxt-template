<?php
declare(strict_types=1);

namespace App\Src\IdentityAccess\Auth\User\UI\Fortify;

use App\Src\IdentityAccess\Auth\User\Application\Request\CreateUserUseCaseRequest;
use App\Src\IdentityAccess\Auth\User\Application\UseCase\CreateUserUseCase;
use App\Src\IdentityAccess\Auth\User\Infrastructure\Eloquent\Model\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUserAction implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function __construct(
        private readonly CreateUserUseCase $createUserUseCase
    ) {
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ], [
            'email.unique' => __('An account with this email already exists. Please sign in or reset your password.'),
        ])->validate();

        $userId = $this->createUserUseCase->execute(new CreateUserUseCaseRequest(
            name: $input['name'],
            email: $input['email'],
            password: $input['password'],
        ));

        return User::query()->findOrFail($userId->toInt());
    }
}
