@extends('admin.layout')

@section('content')
<div class="bg-dragon-surface rounded-xl border border-dragon-border shadow-2xl overflow-hidden">
    <div class="p-6 border-b border-dragon-border flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-dragon-red dragon-text-glow flex items-center">
                <i class="fas fa-link mr-3"></i> REFERRAL MANAGEMENT
            </h2>
            <p class="text-dragon-silver-dark text-sm mt-1">Track and manage promotional links and traffic.</p>
        </div>
        <button onclick="toggleModal('createRefModal')" class="px-5 py-2.5 bg-dragon-red hover:bg-dragon-red-bright text-white font-bold rounded-lg transition-all transform hover:scale-105 flex items-center shadow-lg shadow-dragon-red/20">
            <i class="fas fa-plus mr-2"></i> CREATE LINK
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-dragon-black/50 text-dragon-red uppercase text-xs font-black tracking-widest">
                    <th class="px-6 py-4 border-b border-dragon-border">Name</th>
                    <th class="px-6 py-4 border-b border-dragon-border">Code</th>
                    <th class="px-6 py-4 border-b border-dragon-border">Target URL</th>
                    <th class="px-6 py-4 border-b border-dragon-border text-center">Total Clicks</th>
                    <th class="px-6 py-4 border-b border-dragon-border text-center">Unique Clicks</th>
                    <th class="px-6 py-4 border-b border-dragon-border text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dragon-border">
                @forelse($links as $link)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4">
                        <span class="font-bold text-dragon-silver group-hover:text-dragon-red transition-colors">{{ $link->name }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <code class="px-2 py-1 bg-dragon-black rounded text-dragon-red border border-dragon-border font-mono text-sm">
                            {{ $link->code }}
                        </code>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-dragon-silver-dark text-sm truncate max-w-xs block">{{ $link->target_url }}</span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-blue-500/10 text-blue-400 rounded-full text-xs font-bold border border-blue-500/20">
                            {{ $link->total_clicks }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 bg-green-500/10 text-green-400 rounded-full text-xs font-bold border border-green-500/20">
                            {{ $link->unique_clicks }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.referrals.show', $link->id) }}" class="p-2 bg-dragon-surface hover:bg-dragon-red/20 text-dragon-silver-dark hover:text-dragon-red rounded-lg transition-all border border-dragon-border" title="View Statistics">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <form action="{{ route('admin.referrals.destroy', $link->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this referral link? All click data will be lost.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-dragon-surface hover:bg-red-600/20 text-dragon-silver-dark hover:text-red-500 rounded-lg transition-all border border-dragon-border" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-dragon-silver-dark italic">
                        No referral links created yet. Click "Create Link" to get started.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal -->
<div id="createRefModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4 bg-black/80 backdrop-blur-sm">
        <div class="relative bg-dragon-surface w-full max-w-md rounded-2xl border border-dragon-border shadow-2xl">
            <div class="p-6 border-b border-dragon-border flex items-center justify-between">
                <h3 class="text-xl font-bold text-dragon-red">CREATE REFERRAL LINK</h3>
                <button onclick="toggleModal('createRefModal')" class="text-dragon-silver-dark hover:text-white transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.referrals.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-black text-dragon-red uppercase tracking-widest mb-2">Internal Name</label>
                    <input type="text" name="name" required placeholder="e.g. YouTube Sponsor - RunescapeGuy" 
                           class="w-full bg-dragon-black border border-dragon-border rounded-lg px-4 py-2.5 text-dragon-silver focus:border-dragon-red outline-none transition-all">
                </div>
                <div>
                    <label class="block text-xs font-black text-dragon-red uppercase tracking-widest mb-2">Target URL</label>
                    <input type="text" name="target_url" required value="/"
                           class="w-full bg-dragon-black border border-dragon-border rounded-lg px-4 py-2.5 text-dragon-silver focus:border-dragon-red outline-none transition-all">
                </div>
                <div class="pt-4 flex items-center gap-3">
                    <button type="button" onclick="toggleModal('createRefModal')" class="flex-1 px-4 py-2.5 bg-dragon-black hover:bg-zinc-900 text-dragon-silver font-bold rounded-lg transition-all border border-dragon-border">
                        CANCEL
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-dragon-red hover:bg-dragon-red-bright text-white font-bold rounded-lg transition-all shadow-lg shadow-dragon-red/20">
                        CREATE LINK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }
</script>
@endsection
