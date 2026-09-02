<?php

namespace App\Http\Controllers\Console;

use App\Http\Controllers\Controller;
use App\Http\Requests\Console\ContactRequest;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Per-contact CRUD from a client's detail page. Managing a client's contacts
 * is authorised as updating the client.
 */
class ContactController extends Controller
{
    public function store(ContactRequest $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        DB::transaction(function () use ($request, $client) {
            $contact = $client->contacts()->create($request->contactAttributes());
            $this->enforceSinglePrimary($client, $contact);
            $this->log($client, "Contact “{$contact->name}” added");
        });

        return back()->with('success', 'Contact added.');
    }

    public function update(ContactRequest $request, Client $client, Contact $contact): RedirectResponse
    {
        $this->authorize('update', $client);
        abort_unless($contact->client_id === $client->id, 404);

        DB::transaction(function () use ($request, $client, $contact) {
            $contact->update($request->contactAttributes());
            $this->enforceSinglePrimary($client, $contact);
            $this->log($client, "Contact “{$contact->name}” updated");
        });

        return back()->with('success', 'Contact updated.');
    }

    public function destroy(Client $client, Contact $contact): RedirectResponse
    {
        $this->authorize('update', $client);
        abort_unless($contact->client_id === $client->id, 404);

        $name = $contact->name;
        $contact->delete();
        $this->log($client, "Contact “{$name}” removed");

        return back()->with('success', 'Contact removed.');
    }

    /** If the given contact is primary, clear the flag on the client's others. */
    protected function enforceSinglePrimary(Client $client, Contact $contact): void
    {
        if ($contact->is_primary) {
            $client->contacts()
                ->whereKeyNot($contact->id)
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }
    }

    protected function log(Client $client, string $description): void
    {
        activity()
            ->performedOn($client)
            ->event('contact')
            ->log($description);
    }
}
