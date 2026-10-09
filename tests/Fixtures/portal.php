<?php

$league = ['id' => 1, 'name' => 'Fixture Premier League', 'status' => true, 'image_path' => null];
$home = ['id' => 10, 'name' => 'Fixture United', 'short_code' => 'FIX', 'image_path' => null, 'meta' => ['location' => 'home']];
$away = ['id' => 20, 'name' => 'Sample City', 'short_code' => 'SAM', 'image_path' => null, 'meta' => ['location' => 'away']];
$fixture = ['id' => 100, 'name' => 'Fixture United vs Sample City', 'starting_at' => '2026-10-09 12:00:00', 'league_id' => 1, 'league' => $league, 'state' => ['short_name' => 'FT', 'name' => 'Finished'], 'participants' => [$home, $away], 'scores' => [['description' => 'CURRENT', 'score' => ['participant' => 'home', 'goals' => 0]], ['description' => 'CURRENT', 'score' => ['participant' => 'away', 'goals' => 0]]], 'home_team' => $home, 'away_team' => $away, 'current_home_score' => 0, 'current_away_score' => 0];
$player = ['id' => 30, 'name' => 'Fixture Player', 'display_name' => 'Fixture Player', 'height' => null, 'weight' => null, 'date_of_birth' => null, 'image_path' => null];
$news = ['id' => 1, 'source' => 'Test fixture', 'title' => 'Local fixture: a football news headline for preview', 'description' => 'This content exists only for local interface verification.', 'content' => 'Test fixture article body.', 'url' => 'https://example.com', 'url_to_image' => null, 'published_at' => '2026-10-09T09:00:00Z', 'author' => 'Fixture'];

return compact('league', 'home', 'away', 'fixture', 'player', 'news');
