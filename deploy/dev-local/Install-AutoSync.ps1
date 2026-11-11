$ErrorActionPreference = 'Stop'
$taskName = 'ONCC-Senegal-Dev'
$identity = [System.Security.Principal.WindowsIdentity]::GetCurrent().Name
$syncScript = Join-Path $PSScriptRoot 'Sync-Dev.ps1'

$windowsPowerShell = Join-Path $env:SystemRoot 'System32\WindowsPowerShell\v1.0\powershell.exe'
$action = New-ScheduledTaskAction -Execute $windowsPowerShell `
    -Argument ('-NoProfile -NonInteractive -ExecutionPolicy RemoteSigned -File "{0}"' -f $syncScript) `
    -WorkingDirectory $PSScriptRoot
$triggers = @(
    (New-ScheduledTaskTrigger -AtLogOn -User $identity)
    (New-ScheduledTaskTrigger -Once -At (Get-Date).AddMinutes(1) `
        -RepetitionInterval (New-TimeSpan -Minutes 5) -RepetitionDuration (New-TimeSpan -Days 3650))
)
$settings = New-ScheduledTaskSettingsSet -MultipleInstances IgnoreNew `
    -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Minutes 20)

$principal = New-ScheduledTaskPrincipal -UserId $identity -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $triggers `
    -Settings $settings -Principal $principal -Description 'Synchronise ONCC dev avec GHCR.' -Force | Out-Null
Start-ScheduledTask -TaskName $taskName
Write-Host "Synchronisation ONCC installée. État : Get-ScheduledTaskInfo -TaskName $taskName"
