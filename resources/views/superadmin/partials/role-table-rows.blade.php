@if($activeTab == 'roles')
    @foreach($roles as $role)
        <tr>
            <td>{{ $role->roleid }}</td>
            <td>{{ $role->rolename }}</td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn action-reset" 
                            onclick="openEditRoleModal('{{ $role->roleid }}', '{{ $role->rolename }}')"
                            title="Edit">
                        <i data-lucide="edit-3"></i>
                    </button>
                    <form action="/management/roles/{{ $role->roleid }}/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this role?')">
                        @csrf
                        <button type="submit" class="action-btn" style="color: #d25764;" title="Delete">
                            <i data-lucide="trash-2"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @endforeach

    @if($roles->isEmpty())
        <tr>
            <td colspan="3" style="text-align: center; padding: 20px; color: #64748b;">No roles found.</td>
        </tr>
    @endif
@endif
