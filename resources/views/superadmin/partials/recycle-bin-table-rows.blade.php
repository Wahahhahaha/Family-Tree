@foreach ($users as $user)
    <tr data-user-row data-userid="{{ (int) ($user->userid ?? 0) }}">
        <td class="bulk-select-col">
            <input
                type="checkbox"
                class="bulk-select-checkbox"
                data-bulk-checkbox
                aria-label="{{ __('management_recycle_bin.select_deleted_users') }} {{ $user->username ?? $user->fullname ?? '-' }}"
            >
        </td>
        <td>{{ (int) ($user->userid ?? 0) }}</td>
        <td>{{ $user->username ?? '-' }}</td>
        <td>{{ $user->fullname ?? '-' }}</td>
        <td>{{ $user->levelname ?? '-' }}</td>
        <td>{{ $user->rolename ?? '-' }}</td>
        <td>{{ $user->email ?? '-' }}</td>
        <td>{{ $user->phone ?? '-' }}</td>
        <td>{{ $user->deleted_at ?? '-' }}</td>
        <td>
            <div class="action-group">
                <form method="POST" action="/management/users/{{ (int) ($user->userid ?? 0) }}/restore">
                    @csrf
                    <button type="submit" class="btn btn-soft">{{ __('management_recycle_bin.restore') }}</button>
                </form>
                <form
                    method="POST"
                    action="/management/users/{{ (int) ($user->userid ?? 0) }}/force-delete"
                    data-confirm-message="{{ __('management_recycle_bin.delete_confirm') }}"
                >
                    @csrf
                    <button type="submit" class="btn btn-danger-soft">{{ __('management_recycle_bin.delete') }}</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
