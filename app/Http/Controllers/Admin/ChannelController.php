<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Services\Xmpp\XmppProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function __construct(private readonly XmppProvisioner $xmpp)
    {
    }

    public function index(): View
    {
        return view('admin.channels', [
            'channels' => Channel::orderBy('name')->get(),
        ]);
    }

    /**
     * Create a persistent public channel: the ejabberd room first (so we don't
     * record one we failed to create), then the directory row.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'localpart' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9](?:[a-z0-9._-]*[a-z0-9])?$/', 'unique:channels,localpart'],
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $this->xmpp->createRoom($data['localpart'], $data['name'], $data['description'] ?? '');
        } catch (\Throwable $e) {
            return back()->withErrors(['localpart' => 'Could not create the room on the chat server. Try again.']);
        }

        Channel::create([
            'localpart' => $data['localpart'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', "Created #{$data['localpart']}.");
    }

    public function destroy(Channel $channel): RedirectResponse
    {
        try {
            $this->xmpp->destroyRoom($channel->localpart);
        } catch (\Throwable $e) {
            // Tear down the directory entry regardless; the room may already be gone.
        }

        $channel->delete();

        return back()->with('status', "Deleted #{$channel->localpart}.");
    }
}
