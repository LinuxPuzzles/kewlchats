{{-- FIRST-PASS DRAFT — written to capture the right substance for KewlChats.
     Have a qualified lawyer review before launch. --}}
<x-page title="Privacy Policy">

    <div class="prose-legal">

        <p class="text-sm text-slate-500">Last updated: 18 June 2026</p>

        <h1 class="text-4xl font-extrabold tracking-tight text-slate-100">Privacy Policy</h1>

        <p>
            The short version: <strong>we don't run ads, we don't track you, and we don't sell your
            data — ever.</strong> KewlChats is a free chat service, not a data business. To make chat
            work the way you expect — history, multiple devices, public rooms — our server does store
            your messages. But we don't read them, mine them, or monetize them. This policy explains
            what we collect, why, and how we treat it.
        </p>

        <h2>Who's responsible for your data</h2>
        <p>
            KewlChats is operated by <strong>Powered By Tomorrow, LLC</strong>, based in Orange County,
            Florida, USA. For the purposes of data-protection laws like the GDPR, we are the “data
            controller” for the limited personal information described here. You can reach us about any
            privacy matter at
            <a href="mailto:{{ 'privacy@'.config('xmpp.domain') }}">{{ 'privacy@'.config('xmpp.domain') }}</a>.
        </p>

        <h2>Our approach</h2>
        <p>
            We're not in the business of watching you. We collect the personal details a chat service
            needs and nothing extra — no ad-tech, no behavioural profiling, no selling. Your messages
            are stored so the Service works as you'd expect (scrollback, syncing across your devices),
            but they're yours: we don't read or use them. Put plainly — the house isn't monitoring
            you, but it does have windows. Your messages exist on our server, so use the Service
            accordingly.
        </p>

        <h2>What we collect</h2>
        <ul>
            <li><strong>Account details:</strong> the display name and email address you give us, and
                the username that becomes your chat address.</li>
            <li><strong>Your password:</strong> stored only as a secure, one-way hash. We can't read
                it, and we never show it back to you.</li>
            <li><strong>Email address:</strong> used to verify your account, send password resets, and
                contact you about important account or service matters. We don't send marketing email.</li>
            <li><strong>Limited technical data:</strong> to run the Service and keep it secure, our
                servers may briefly process connection information such as IP addresses and basic
                connection logs. We keep this for as short a time as practical and don't use it to
                build a profile of you.</li>
            <li><strong>Bot protection:</strong> our sign-up and account forms use Unbotable, a
                self-hosted, privacy-respecting bot filter. It runs on our own infrastructure — no
                third party, no tracking, and no CAPTCHAs to solve — and stores only one-way,
                anonymous hashes that can't be traced back to you and expire on their own.</li>
        </ul>

        <h2>Your messages</h2>
        <p>
            So that chat works the way you expect — recent history, your phone and laptop staying in
            sync, public rooms with scrollback — our server keeps an archive of messages. <strong>We
            store them; we don't read, analyse, or sell them.</strong> Where your chat app uses
            end-to-end encryption (such as OMEMO), the content is ciphertext we couldn't read even if
            we wanted to. Messages you post in a <strong>public room are visible to everyone in that
            room</strong> by their nature.
        </p>

        <h2>What we don't do</h2>
        <ul>
            <li><strong>No advertising or tracking.</strong> No ad networks, no tracking pixels, no
                third-party analytics following you around.</li>
            <li><strong>No selling or renting your data.</strong> Not to advertisers, not to data
                brokers, not to anyone.</li>
            <li><strong>No profiles.</strong> We don't build a profile of your interests or behaviour.</li>
            <li><strong>No snooping.</strong> Reading your conversations isn't something we do — your
                messages are stored to run the Service, not for us to look through.</li>
        </ul>

        <h2>Encryption</h2>
        <p>
            Many chat apps support end-to-end encryption (such as OMEMO). Where it's in use, message
            content is readable only by you and the people you're chatting with — not by us. Because
            we don't control which app you use, and encryption isn't always on (especially in public
            rooms), we can't promise every message is encrypted. Where a message <em>isn't</em>
            end-to-end encrypted, its content is technically readable on our server — we don't read it
            as a matter of practice, but you should treat public rooms and unencrypted chats with that
            in mind.
        </p>

        <h2>Cookies</h2>
        <p>
            We use a single essential cookie to keep you signed in. We don't use advertising or
            tracking cookies.
        </p>

        <h2>How we use what little we have</h2>
        <ul>
            <li>To create and run your account and deliver the Service.</li>
            <li>To keep KewlChats secure and to prevent spam, abuse, and fraud.</li>
            <li>To contact you about your account or important service changes.</li>
            <li>To meet legal obligations where we're required to.</li>
        </ul>

        <h2>Who we share with</h2>
        <p>
            We don't sell your data. We share information only with the few service providers we need
            to operate — for example, Cloudflare (bot protection) and our email delivery provider —
            and only as much as they need to do their job.
        </p>
        <p>
            <strong>Legal requests.</strong> If we're legally compelled to provide information, we can
            only ever hand over what we actually have. We do keep a message archive, so where messages
            aren't end-to-end encrypted that could include message content; where your app uses OMEMO,
            what we hold is ciphertext we can't read. We don't volunteer your data, we resist
            overbroad requests, and we comply only as far as the law requires.
        </p>

        <h2>Talking to other chat services (federation)</h2>
        <p>
            KewlChats currently operates as a closed network — your messages stay within KewlChats. If
            we enable connections to other chat servers in the future, messages you send to people on
            those servers will travel to and be handled by those servers, which are outside our
            control. We'll update this policy before that changes.
        </p>

        <h2>How long we keep things</h2>
        <ul>
            <li><strong>Account details:</strong> kept while your account is active.</li>
            <li><strong>Technical/connection data:</strong> kept only briefly, then removed.</li>
            <li><strong>Messages:</strong> your message archive is kept while your account is active.
                Deleting your account removes your account, chat identity, and your one-to-one and
                private-group archive; messages you posted in public rooms may remain as part of that
                room's history.</li>
            <li>When you delete your account, we remove your account and chat identity.</li>
        </ul>

        <h2>Your choices</h2>
        <p>
            You can update your profile details or delete your account at any time from your profile
            page. To make any privacy request, email
            <a href="mailto:{{ 'privacy@'.config('xmpp.domain') }}">{{ 'privacy@'.config('xmpp.domain') }}</a>.
            We'll respond within the time required by the laws that apply to you, and we may need to
            verify your identity first.
        </p>

        <h2>Legal bases we rely on (GDPR)</h2>
        <p>
            If you're in the EU, EEA, or UK, we process your personal information only where we have a
            lawful basis to do so:
        </p>
        <ul>
            <li><strong>To provide the Service you asked for</strong> (performance of a contract):
                creating and running your account, verifying your email, and delivering your messages.</li>
            <li><strong>Our legitimate interests:</strong> keeping KewlChats secure, preventing spam
                and abuse, and running the service reliably — balanced against your privacy, which is
                why we collect so little.</li>
            <li><strong>Legal obligation:</strong> where we're required to act by law.</li>
            <li><strong>Consent:</strong> where we ask for it; you can withdraw it at any time.</li>
        </ul>

        <h2>Your rights in the EU, EEA &amp; UK (GDPR)</h2>
        <p>You have the right to:</p>
        <ul>
            <li><strong>Access</strong> the personal information we hold about you.</li>
            <li><strong>Correct</strong> information that's wrong or incomplete.</li>
            <li><strong>Delete</strong> your information (“right to be forgotten”).</li>
            <li><strong>Restrict</strong> or <strong>object</strong> to certain processing.</li>
            <li><strong>Portability</strong> — receive your information in a usable format.</li>
            <li><strong>Withdraw consent</strong> at any time, where we relied on it.</li>
            <li><strong>Complain</strong> to your local data-protection authority if you're unhappy
                with how we've handled your information.</li>
        </ul>
        <p>
            To exercise any of these, email
            <a href="mailto:{{ 'privacy@'.config('xmpp.domain') }}">{{ 'privacy@'.config('xmpp.domain') }}</a>.
            We'll provide a copy of the personal information we hold about you.
        </p>

        <h2>Your rights in California (CCPA/CPRA)</h2>
        <p>
            If you're a California resident, you have the right to know what personal information we
            collect, to access and delete it, to correct it, and to not be discriminated against for
            exercising these rights. In the past 12 months we have collected these categories of
            personal information: <strong>identifiers</strong> (such as name, email, username, and IP
            address), <strong>limited internet/network activity</strong> (basic connection data), and
            the <strong>content of your communications</strong> (the messages we store to provide the
            Service). We collect it directly from you and automatically from your device, and use it
            for the purposes described in this policy — we do not sell it.
        </p>
        <p>
            <strong>We do not “sell” or “share” your personal information</strong> as those terms are
            defined under California law, and we have not done so in the past 12 months. Because we
            don't sell or share, there's nothing for you to opt out of — but you're always welcome to
            ask us anything. You may use an authorised agent to make a request on your behalf. To
            exercise your rights, email
            <a href="mailto:{{ 'privacy@'.config('xmpp.domain') }}">{{ 'privacy@'.config('xmpp.domain') }}</a>.
        </p>

        <h2>International data transfers</h2>
        <p>
            KewlChats is operated from the United States, and our servers are located there. If you use
            KewlChats from outside the US — including from the EU, EEA, or UK — your information is
            processed in the United States. US privacy laws may differ from those in your country; we
            rely on appropriate safeguards and on the fact that we hold very little information about
            you. Contact us if you'd like more detail.
        </p>

        <h2>Children</h2>
        <p>
            KewlChats isn't intended for children under 13 (or the minimum age in your country). We
            don't knowingly collect information from children under that age; if you believe a child
            has signed up, contact us and we'll remove the account.
        </p>

        <h2>Security</h2>
        <p>
            We protect your information with measures like password hashing, encrypted connections, and
            keeping the personal details we collect about you to a minimum. No service can promise
            perfect security, and your strongest protection for sensitive content is an app with
            end-to-end encryption turned on.
        </p>

        <h2>Changes to this policy</h2>
        <p>
            We may update this policy from time to time. We'll change the date at the top and, for
            significant changes, give reasonable notice.
        </p>

        <h2>Contact</h2>
        <p>
            Questions about your privacy? Email
            <a href="mailto:{{ 'privacy@'.config('xmpp.domain') }}">{{ 'privacy@'.config('xmpp.domain') }}</a>.
        </p>

    </div>

</x-page>
