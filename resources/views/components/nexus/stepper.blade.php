@props([
    'steps' => [],
    'currentStep' => 1
])

<div class="nexus-stepper mb-4">
    <div class="stepper-track">
        @foreach($steps as $index => $step)
            @php
                $stepNumber = $index + 1;
                $isCompleted = $stepNumber < $currentStep;
                $isActive = $stepNumber == $currentStep;
            @endphp
            <div class="stepper-item {{ $isCompleted ? 'completed' : '' }} {{ $isActive ? 'active' : '' }}">
                <div class="step-badge">
                    @if($isCompleted)
                        <i class="fa-solid fa-check"></i>
                    @else
                        <span>{{ $stepNumber }}</span>
                    @endif
                </div>
                <div class="step-label">
                    <span class="step-title">{{ $step['title'] ?? 'Step ' . $stepNumber }}</span>
                    @if(isset($step['subtitle']))
                        <span class="step-sub">{{ $step['subtitle'] }}</span>
                    @endif
                </div>
            </div>

            @if(!$loop->last)
                <div class="stepper-line {{ $stepNumber < $currentStep ? 'completed' : '' }}"></div>
            @endif
        @endforeach
    </div>
</div>
