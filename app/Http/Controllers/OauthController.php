<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OauthController extends Controller
{
    public function __construct()
    {
        $this->OAUTH2_CLIENT_ID = '850366752359-c6delcgpfao8bcsokafpqmkj0m5a7e4k.apps.googleusercontent.com';
        $this->OAUTH2_CLIENT_SECRET = 'szI462A2-6ak6qTqnR0JDxmi';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function callback(Request $request)
    {
        $redirect = $request->get('redirect');
        $code = $request->get('code');

        if (!isset($code) || !$code) return back();

        $googleClient = new \Google_Client();
        $googleClient->setClientId($this->OAUTH2_CLIENT_ID);
        $googleClient->setClientSecret($this->OAUTH2_CLIENT_SECRET);
        $googleClient->setAccessType("offline");
        $googleClient->setScopes('https://www.googleapis.com/auth/youtube');

        $redirectUrl = ($redirect)? url($redirect) : '/';
        $redirect = filter_var($redirectUrl, FILTER_SANITIZE_URL);
        $googleClient->setRedirectUri($redirect);

        $googleClient->authenticate($code);

        $token = $googleClient->getAccessToken();

        $request->session()->put('access_token', $token['access_token']);

        $redirectUrl = url('/');

        header('Location: ' . filter_var($redirectUrl, FILTER_SANITIZE_URL));
    }
}
