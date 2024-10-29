<mjml>
    <mj-head>
      <mj-title>Claim Approval Request</mj-title>
      <mj-font name="Inter" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" />
    </mj-head>
    <mj-body background-color="#ffffff">
      <!-- Logo Section -->
      <mj-section padding="50px 0">
        <mj-column>
          <mj-image width="180px" src="{{ asset('images/logo.png') }}" alt="Logo" />
        </mj-column>
      </mj-section>
  
      <!-- Content Section -->
      <mj-section>
        <mj-column>
          <mj-text align="center" font-size="24px" font-weight="600" color="#111827">
            Claim Approval Request
          </mj-text>
        </mj-column>
      </mj-section>
  
      <!-- Claim Details -->
      <mj-section background-color="#f9fafb" border-radius="12px">
        <mj-column>
          <mj-text color="#6b7280" font-size="14px">Claim ID</mj-text>
          <mj-text color="#111827" font-size="16px" font-weight="500">{{ $claim->id }}</mj-text>
          
          <mj-text color="#6b7280" font-size="14px">Claim Title</mj-text>
          <mj-text color="#111827" font-size="16px" font-weight="500">{{ $claim->title }}</mj-text>
          
          <mj-text color="#6b7280" font-size="14px">Submitted By</mj-text>
          <mj-text color="#111827" font-size="16px" font-weight="500">{{ $claim->user->name }}</mj-text>
          
          <mj-text color="#6b7280" font-size="14px">Status</mj-text>
          <mj-text color="#111827" font-size="16px" font-weight="500">{{ str_replace('_', ' ', $claim->status) }}</mj-text>
        </mj-column>
      </mj-section>
  
      <!-- Locations -->
      <mj-section>
        <mj-column>
          <mj-text font-size="18px" font-weight="600" color="#111827">Locations</mj-text>
          @foreach($locations as $location)
          <mj-text background-color="#f9fafb" padding="16px" border-radius="8px" color="#111827" font-size="14px">
            {{ $location->location }}
          </mj-text>
          @endforeach
        </mj-column>
      </mj-section>
  
      <!-- Action Buttons -->
      <mj-section>
        <mj-column>
          <mj-button href="{{ route('claims.email.action', ['id' => $claim->id, 'action' => 'approve']) }}"
                     background-color="#10b981" color="#ffffff" border-radius="6px">
            Approve
          </mj-button>
        </mj-column>
        <mj-column>
          <mj-button href="{{ route('claims.email.action', ['id' => $claim->id, 'action' => 'reject']) }}"
                     background-color="#ef4444" color="#ffffff" border-radius="6px">
            Reject
          </mj-button>
        </mj-column>
      </mj-section>
    </mj-body>
  </mjml>