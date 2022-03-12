<?php
/*
 * Copyright (c) 2022.
 *
 * @author Syber
 */

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Правила валидации полей запроса
     *
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'name'     => 'required|max:100',
            'username' => [
                'required',
                'regex:/[0-9a-zA-Z.-]/i',
                'min:3',
                'max:100',
                Rule::unique('users')->ignore($this->id)
            ],
            'email'    => [
                'required',
                'email:rfc,dns',
                Rule::unique('users')->ignore($this->id)
            ],
            'password' => [
                'required_without:id',
                'required_with:password_confirmation',
                'confirmed',
                'max:100',
                'exclude_without:password_confirmation'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'username.regex'            => 'Имя пользователя должно состоять только из латинских букв, цифр, точки или
                знака подчеркивания.',
            'username.unique'           => 'Пользователь с данным логином уже зарегистрирован.',
            'email.unique'              => 'Пользователь с данным e-mail адресом уже зарегистрирован.',
            'password.required_without' => 'Обязательно укажите пароль для авторизации.',
            'password.confirmed'        => 'Введенный пароль не совпадает с подтверждаемым.'
        ];
    }
}
