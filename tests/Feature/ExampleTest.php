<?php

it('redirects guests from the home page to login', function () {
    $this->get('/')->assertRedirect('/settings/profile');
    $this->get('/settings/profile')->assertRedirect('/login');
});
