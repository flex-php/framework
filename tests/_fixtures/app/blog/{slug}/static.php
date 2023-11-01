<?php

$posts = [
    [
        "slug" => "hello-world",
        "title" => "Hello World",
        "content" => "Lorem ipsum dolor sit amet, consectetur",
    ],
    [
        "slug" => "another-post",
        "title" => "Another Post",
        "content" => "This is a second post",
    ]
];

return [
    "getStaticPaths" => function () use ($posts) {

        return [
            "paths" => array_map(function ($post) {
                return [
                    "params" => [
                        "slug" => $post["slug"]
                    ]
                ];
            }, $posts)
        ];
    },

    "getStaticData" => function ($params) use ($posts) {
        $post = array_filter($posts, function ($post) use ($params) {
            return $post["slug"] == $params["slug"];
        });

        if (count($post) == 0) {
            return null;
        }

        return array_values($post)[0];
    }
];