<?php

it('redirects guests from the home page to login', function () {
    $this->get('/')->assertRedirect('/requests');
    $this->get('/requests')->assertRedirect('/login');
});
