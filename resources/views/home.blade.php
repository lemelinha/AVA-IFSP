
@role('cti')
    <h1>Welcome, Admin!</h1>
    <p>You have full access to the system.</p>
@else
    <h1>Welcome, User!</h1>
    <p>You have limited access to the system.</p>
@endrole

