@extends('layouts.app')

@section('title', 'Digital Graveyard')

<?php $pageClass = 'page-family-tree'; ?>

@section('styles')
<style>
    .graveyard-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    #graveyardMap { height: 600px; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .graveyard-header { margin-bottom: 30px; }
    .marker-popup { text-align: center; }
    .marker-popup img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }
    .marker-popup h3 { margin: 0; font-size: 1rem; color: #1a365d; }
    .marker-popup p { margin: 5px 0 0; font-size: 0.8rem; color: #718096; }
</style>
@endsection

@section('content')
<div class="graveyard-container">
    <div class="graveyard-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-family: 'Sora', sans-serif; font-weight: 800; color: #1a365d; margin: 0;">Digital Graveyard</h1>
                <p style="color: #718096; margin-top: 5px;">Honoring our ancestors and their final resting places.</p>
            </div>
            <a href="/">
                <button class="btn btn-soft"><i data-lucide="arrow-left" style="width: 16px; margin-right: 8px;"></i> Back to Tree</button>
            </a>
        </div>
    </div>

    <div id="graveyardMap"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('graveyardMap').setView([-6.2088, 106.8456], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var markers = [];
    <?php foreach($deceasedMembers as $member): ?>
        <?php 
            $photo = $member->picture 
                ? (preg_match('#^https?://#i', $member->picture) ? $member->picture : asset('uploads/member/' . $member->picture))
                : asset('images/default-avatar.png');
        ?>
        var marker = L.marker([<?php echo $member->burial_latitude; ?>, <?php echo $member->burial_longitude; ?>]).addTo(map);
        marker.bindPopup(`
            <div class="marker-popup">
                <img src="<?php echo $photo; ?>">
                <h3><?php echo addslashes($member->name); ?></h3>
                <p>RIP: <?php echo date('d M Y', strtotime($member->deaddate)); ?></p>
                <?php if($member->burial_location): ?><p style="font-style: italic;"><?php echo addslashes($member->burial_location); ?></p><?php endif; ?>
                <a href="/member/<?php echo $member->memberid; ?>/wiki" style="font-size: 0.7rem; color: #3182ce; text-decoration: none;">View Biography</a>
            </div>
        `);
        markers.push(marker);
    <?php endforeach; ?>

    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});
</script>
@endsection
