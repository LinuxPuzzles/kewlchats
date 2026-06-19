{{-- FIRST-PASS DRAFT — written to capture the right substance for KewlChats.
     Have a qualified lawyer review before launch. --}}
<x-page title="Terms of Service">

    <div class="prose-legal">

        <p class="text-sm text-slate-500">Last updated: 18 June 2026</p>

        <h1 class="text-4xl font-extrabold tracking-tight text-slate-100">Terms of Service</h1>

        <p>
            Welcome to KewlChats. We try to keep things simple and fair, so we've written these
            terms in plain language. By creating an account or using KewlChats (the “Service”) at
            {{ config('xmpp.domain') }}, you agree to these Terms and to our
            <a href="{{ route('privacy') }}">Privacy Policy</a>. If you don't agree, please don't
            use the Service.
        </p>

        <p>
            KewlChats is a free chat service operated by <strong>Powered By Tomorrow, LLC</strong>,
            based in Orange County, Florida, USA (“we”, “us”).
        </p>

        <h2>1. Who can use KewlChats</h2>
        <ul>
            <li>You must be at least <strong>13 years old</strong> (or older if your country
                requires it) to create an account.</li>
            <li>You agree to give accurate information at sign-up and to keep your account secure.</li>
            <li>One person, one account, used by a real human — automated/bulk account creation
                isn't allowed.</li>
        </ul>

        <h2>2. Your account and your password</h2>
        <p>
            When you sign up you choose a username, which becomes your permanent chat address
            (<span class="font-mono">{{ 'you@'.config('xmpp.domain') }}</span>). Because of how chat
            addresses work, <strong>usernames can't be changed or transferred</strong> later, so
            choose one you're happy with.
        </p>
        <p>
            The password you choose is used both to sign in here and to connect your chat apps. We
            store it only in a securely hashed form and <strong>never display it back to you</strong>.
            You're responsible for keeping it secret and for everything that happens under your
            account. Tell us right away if you think someone else has access.
        </p>

        <h2>3. What the Service is (and isn't)</h2>
        <p>
            KewlChats gives you a chat identity and access to one-to-one messages, public and private
            group rooms, and voice/video calls, which you can use from your browser or a compatible
            chat app. To make this work, the Service stores your messages so you get history and your
            devices stay in sync. We don't read, mine, or sell them — see the
            <a href="{{ route('privacy') }}">Privacy Policy</a> for exactly what we keep and how we
            treat it. <strong>Public rooms are public</strong>, and for anything truly sensitive, use a
            chat app with end-to-end encryption.
        </p>
        <p>
            It's a free service provided “as is”. We may add, change, or remove features, and we may
            pause or stop the Service, at any time. We'll try to be reasonable, but we don't promise
            constant availability, message delivery, or that anything you send will be stored.
        </p>

        <h2>4. Acceptable Use Policy (AUP)</h2>
        <p>
            This is the important part. KewlChats is meant to be a friendly place. <strong>You agree
            not to use it to</strong>:
        </p>
        <ul>
            <li><strong>Anything involving children:</strong> we have <strong>zero tolerance</strong>
                for child sexual abuse material (CSAM) or any sexualization of minors. We will remove
                it, terminate the account, and report it to the appropriate authorities.</li>
            <li>Break the law, or help anyone else break the law.</li>
            <li>Harass, threaten, stalk, bully, or incite violence against people; promote hatred or
                terrorism.</li>
            <li>Send spam, bulk unsolicited messages, scams, phishing, or chain messages.</li>
            <li>Distribute malware, viruses, or links intended to harm or deceive.</li>
            <li>Infringe other people's intellectual property or share content you have no right to
                share.</li>
            <li>Impersonate other people or organisations, or misrepresent who you are.</li>
            <li>Attempt to break, overload, probe, or gain unauthorised access to the Service, other
                users, or our infrastructure — including abusing our voice/video relay servers to
                route traffic that isn't a genuine call.</li>
            <li>Evade a ban, suspension, or any limit we've put in place.</li>
        </ul>
        <p>
            <strong>Public rooms are public.</strong> Anything you say in a public room can be seen by
            anyone in it, may be relayed to others, and is your responsibility. Don't share anything in
            public you wouldn't want public.
        </p>

        <h2>5. Reporting abuse &amp; enforcement</h2>
        <p>
            If you see something that breaks these rules, email <a href="mailto:{{ 'abuse@'.config('xmpp.domain') }}">{{ 'abuse@'.config('xmpp.domain') }}</a>.
            We can suspend or terminate any account, remove access, or limit use of the Service at our
            discretion — especially for anything in the AUP above. For illegal content we will
            cooperate with law enforcement as required by law.
        </p>

        <h2>6. Your content</h2>
        <p>
            You keep ownership of what you write and send. You grant us only the limited permission
            needed to operate the Service — to transmit your messages to their recipients, store them
            so you and your recipients get history and multi-device sync, and display them back to the
            people they were sent to. This permission is solely to run the Service; we don't use your
            content for anything else, and we don't sell it.
        </p>

        <h2>7. Encryption</h2>
        <p>
            Many chat apps support end-to-end encryption (such as OMEMO), which keeps message content
            readable only by you and the people you're talking to. Where it's used, we can't read that
            content. However, <strong>we don't control the apps you choose and can't guarantee
            encryption is always on</strong> — particularly in public rooms — so please don't treat the
            Service as guaranteed-private for sensitive information. See the
            <a href="{{ route('privacy') }}">Privacy Policy</a> for what we do and don't keep.
        </p>

        <h2>8. No warranty</h2>
        <p>
            The Service is provided “as is” and “as available”, without warranties of any kind, whether
            express or implied. We don't warrant that it will be uninterrupted, secure, error-free, or
            that messages will be delivered or retained.
        </p>

        <h2>9. Limitation of liability</h2>
        <p>
            To the fullest extent permitted by law, KewlChats and its operator will not be liable for
            any indirect, incidental, special, or consequential damages, or for loss of data, messages,
            or profits, arising from your use of (or inability to use) the Service. This is a free
            service, and our total liability to you is limited accordingly.
        </p>

        <h2>10. Ending your use</h2>
        <p>
            You can stop using KewlChats at any time and delete your account from your profile page,
            which removes your account and chat identity. We may suspend or terminate accounts that
            break these terms, or if we stop offering the Service.
        </p>

        <h2>11. Changes to these terms</h2>
        <p>
            We may update these terms from time to time. If we make significant changes, we'll update
            the date above and, where reasonable, let you know. Continuing to use the Service after a
            change means you accept the updated terms.
        </p>

        <h2>12. Governing law &amp; contact</h2>
        <p>
            These terms are governed by the laws of the <strong>State of Florida, USA</strong>, without
            regard to its conflict-of-laws rules. You agree that the exclusive venue for any dispute
            relating to these terms or the Service is the state or federal courts located in
            <strong>Orange County, Florida</strong>.
        </p>
        <p>
            Questions about these terms? Email
            <a href="mailto:{{ 'support@'.config('xmpp.domain') }}">{{ 'support@'.config('xmpp.domain') }}</a>.
        </p>

    </div>

</x-page>
