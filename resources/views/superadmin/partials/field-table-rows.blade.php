@if($activeTab == 'fields')
    @foreach($fields as $field)
        <tr>
            <td>{{ $field->id }}</td>
            <td>{{ $field->field_name }}</td>
            <td><span class="badge" style="background:#e2f2fc; color:#1f9ad6; padding:2px 8px; border-radius:4px; font-size:12px;">{{ strtoupper($field->field_type) }}</span></td>
            <td>{{ $field->is_required ? 'Yes' : 'No' }}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn action-reset" 
                            onclick="openEditFieldModal('{{ $field->id }}', '{{ $field->field_name }}', '{{ $field->field_type }}', '{{ $field->field_options }}', '{{ $field->is_required }}')"
                            title="Edit">
                        <i data-lucide="edit-3"></i>
                    </button>
                    <form action="/management/fields/{{ $field->id }}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this field?')">
                        @csrf
                        <button type="submit" class="action-btn" style="color: #d25764;" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach

    @if($fields->isEmpty())
        <tr>
            <td colspan="5" style="text-align: center; padding: 20px; color: #64748b;">No custom fields found.</td>
        </tr>
    @endif
@endif
