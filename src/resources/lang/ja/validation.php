<?php

return [
    'required' => ':attribute を入力してください',
    'email' => ':attribute は有効なメールアドレス形式で入力してください',
    'min' => [
        'string' => ':attribute は :min 文字以上で入力してください',
    ],
    'max' => [
        'string' => ':attribute は :max 文字以内で入力してください',
    ],
    'confirmed' => ':attribute が確認用と一致していません',
    'unique' => 'その :attribute は既に使用されています',
    'exists' => '選択された :attribute は無効です',
    'string' => ':attribute は文字列で入力してください',
    'password' => 'パスワードが正しくありません',
    'attributes' => [
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'name' => '名前',
    ],
];
