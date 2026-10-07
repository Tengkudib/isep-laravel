    <tr>
        <td class="fw-semibold">{{ $u['name'] }}</td>
        <td><code>{{ $u['username'] }}</code></td>
        <td>{{ $u['email'] ?? '-' }}</td>
        @if ($role_key === 'student')<td>{{ $u['student_id'] ?? '-' }}</td>@endif
        <td>
            <form method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="id" value="{!! $u['id'] !!}">
                <input type="hidden" name="new_status" value="{!! $u['status'] === 'active' ? 'inactive' : 'active' !!}">
                <button type="submit" name="toggle_status" class="btn btn-sm {!! $u['status']==='active' ? 'btn-outline-success' : 'btn-outline-secondary' !!}">
                    {!! $u['status'] === 'active' ? t('Aktif', 'Active') : t('Tidak Aktif', 'Inactive') !!}
                </button>
            </form>
        </td>
        <td class="text-center text-nowrap">
            <form method="POST" class="d-inline" onsubmit="return isepAskNewPassword(this);">
                @csrf
                <input type="hidden" name="id" value="{!! $u['id'] !!}">
                <input type="hidden" name="new_password" value="">
                <button type="submit" name="reset_password" class="btn btn-sm btn-outline-warning" title="{{ t('Tetapkan semula kata laluan', 'Reset password') }}"><i class="fas fa-key"></i></button>
            </form>
            @if ($u['id'] != $current_user_id)
            <form method="POST" class="d-inline" onsubmit="return confirm('{{ t('Padam pengguna ini?', 'Delete this user?') }}');">
                @csrf
                <input type="hidden" name="id" value="{!! $u['id'] !!}">
                <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
            </form>
            @else
            <span class="badge bg-light text-muted">{{ t('Anda', 'You') }}</span>
            @endif
        </td>
    </tr>
    
