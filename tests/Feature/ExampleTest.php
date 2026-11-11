<?php

test('login screen can be rendered', function () {
    $this->get('/login')->assertOk();
});
