import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/MeetingRoom/**/*.php',
        './resources/views/filament/meeting-room/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
