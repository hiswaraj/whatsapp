<?php

use Illuminate\Support\Facades\Route;

//Normal Routes, Tracking Routes
Route::namespace('App\\Http\\Controllers')->group(function () {
    Route::get('/', 'AuthController@home')->name('home');
    Route::get('/tracking', 'TrackingController@index')->name('tracking'); //TODO: TEMP URL FOR TRACKING
    Route::post('/login-api', 'AuthController@login_api')->name('login-api');
    Route::get('/forgot-password', 'AuthController@show_forgot_password')->name('forgot-password');
    Route::post('/forgot-password', 'AuthController@forgot_password')->name('forgot-password.submit');
    Route::get('/reset-password/{token}', 'AuthController@show_reset_password')->name('password.reset');
    Route::post('/reset-password', 'AuthController@reset_password')->name('password.update');


    // Meta Webhooks (Public)
    Route::get('/webhook/meta', 'WebhookController@verifyChallenge')->name('webhook.meta.verify');
    Route::post('/webhook/meta', 'WebhookController@handleWebhook')->name('webhook.meta.handle');
    Route::get('/webhook/whatsapp/{verify_token}', 'WebhookController@verifyChallenge')->name('webhook.whatsapp.verify');
    Route::post('/webhook/whatsapp/{verify_token}', 'WebhookController@handleWebhook')->name('webhook.whatsapp.handle');

    // Protected Dashboard Placeholders
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/dashboard', 'DashboardController@admin')->name('admin.dashboard');
        Route::get('/dashboard', 'DashboardController@user')->name('user.dashboard');

        // Contacts
        Route::get('/contacts', 'ContactController@index')->name('contacts.index');
        Route::post('/contacts', 'ContactController@store')->name('contacts.store');
        Route::put('/contacts/{id}', 'ContactController@update')->name('contacts.update');
        Route::delete('/contacts/{id}', 'ContactController@destroy')->name('contacts.destroy');
        Route::post('/contacts/import', 'ContactController@import')->name('contacts.import');
        Route::get('/contacts/export', 'ContactController@export')->name('contacts.export');

        // Contact Groups
        Route::get('/groups', 'ContactGroupController@index')->name('groups.index');
        Route::post('/groups', 'ContactGroupController@store')->name('groups.store');
        Route::put('/groups/{id}', 'ContactGroupController@update')->name('groups.update');
        Route::delete('/groups/{id}', 'ContactGroupController@destroy')->name('groups.destroy');
        Route::post('/groups/assign', 'ContactGroupController@assignContacts')->name('groups.assign');
        Route::post('/groups/remove', 'ContactGroupController@removeContacts')->name('groups.remove');

        // WABAs (WhatsApp Business Accounts)
        Route::get('/wabas', 'WabaController@index')->name('wabas.index');
        Route::post('/wabas', 'WabaController@store')->name('wabas.store');
        Route::put('/wabas/{id}', 'WabaController@update')->name('wabas.update');
        Route::delete('/wabas/{id}', 'WabaController@destroy')->name('wabas.destroy');
        Route::post('/wabas/{id}/toggle-status', 'WabaController@toggleStatus')->name('wabas.toggle-status');
        Route::post('/wabas/{id}/verify', 'WabaController@verifyConnection')->name('wabas.verify');
        Route::post('/wabas/{id}/test-message', 'WabaController@testMessage')->name('wabas.test-message');
        Route::post('/wabas/{id}/regenerate-token', 'WabaController@regenerateVerifyToken')->name('wabas.regenerate-token');
        Route::post('/wabas/{id}/sync-dp', 'WabaController@syncDp')->name('wabas.sync-dp');
        Route::post('/wabas/{id}/upload-dp', 'WabaController@uploadDp')->name('wabas.upload-dp');

        // Templates
        Route::get('/templates', 'TemplateController@index')->name('templates.index');
        Route::post('/templates', 'TemplateController@store')->name('templates.store');
        Route::delete('/templates/{id}', 'TemplateController@destroy')->name('templates.destroy');
        Route::post('/templates/sync', 'TemplateController@sync')->name('templates.sync');

        // Live Chat
        Route::get('/chat', 'ChatController@index')->name('chat.index');
        Route::get('/chat/conversations', 'ChatController@conversations')->name('chat.conversations');
        Route::get('/chat/conversations/{id}/messages', 'ChatController@messages')->name('chat.messages');
        Route::post('/chat/messages', 'ChatController@sendMessage')->name('chat.messages.send');
        Route::get('/chat/start-contact/{contact_id}', 'ChatController@startChatWithContact')->name('chat.start-contact');
        Route::post('/chat/start', 'ChatController@startChat')->name('chat.start');

        // Media Library
        Route::get('/media', 'MediaLibraryController@index')->name('media.index');
        Route::post('/media', 'MediaLibraryController@store')->name('media.store');
        Route::delete('/media/{id}', 'MediaLibraryController@destroy')->name('media.destroy');
        Route::get('/media/picker', 'MediaLibraryController@picker')->name('media.picker');

        // Campaigns
        Route::get('/campaigns', 'CampaignController@index')->name('campaigns.index');
        Route::get('/campaigns/create', 'CampaignController@create')->name('campaigns.create');
        Route::post('/campaigns', 'CampaignController@store')->name('campaigns.store');
        Route::get('/campaigns/{id}', 'CampaignController@show')->name('campaigns.show');
        Route::post('/campaigns/{id}/action', 'CampaignController@action')->name('campaigns.action');
        Route::delete('/campaigns/{id}', 'CampaignController@destroy')->name('campaigns.destroy');
        Route::get('/campaigns/{id}/export', 'CampaignController@exportLogs')->name('campaigns.export-logs');

        // Quick Broadcasts
        Route::get('/quick-broadcast', 'QuickBroadcastController@index')->name('quick-broadcast.index');
        Route::get('/quick-broadcast/sample', 'QuickBroadcastController@downloadSample')->name('quick-broadcast.sample');
        Route::post('/quick-broadcast/parse', 'QuickBroadcastController@parse')->name('quick-broadcast.parse');
        Route::post('/quick-broadcast/send', 'QuickBroadcastController@send')->name('quick-broadcast.send');

        // Flow Builder / Chatbot Flows
        Route::get('/flows', 'FlowController@index')->name('flows.index');
        Route::get('/flows/create', 'FlowController@create')->name('flows.create');
        Route::post('/flows', 'FlowController@store')->name('flows.store');
        Route::get('/flows/{id}/edit', 'FlowController@edit')->name('flows.edit');
        Route::put('/flows/{id}', 'FlowController@update')->name('flows.update');
        Route::delete('/flows/{id}', 'FlowController@destroy')->name('flows.destroy');
        Route::post('/flows/{id}/toggle-status', 'FlowController@toggleStatus')->name('flows.toggle-status');

        // Logout route
        Route::post('/logout', 'AuthController@logout')->name('logout');
    });
});
