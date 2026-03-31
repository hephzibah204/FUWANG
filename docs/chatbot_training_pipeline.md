# AI Chatbot Training Pipeline Architecture & Methodology

## 1. Overview
This document outlines the architecture, training methodology, and continuous learning pipeline for transforming the Fuwa.NG support chatbot into an expert-level, context-aware AI system capable of answering 100% of user inquiries. 

The pipeline employs a hybrid approach: **Retrieval-Augmented Generation (RAG)** combined with **Supervised Fine-Tuning (SFT)** and **Few-Shot Prompting**, ensuring the system provides accurate, personalized, and context-aware responses while maintaining high precision and recall.

## 2. Model Architecture
The chatbot utilizes **Google Gemini Pro** as the core reasoning engine, augmented by a custom semantic search layer.

### 2.1 Component Stack
- **Base Model:** Google Gemini Pro (`gemini-pro`)
- **Embedding Model:** Google text-embedding-004
- **Vector Database:** PostgreSQL/MySQL with Vector extensions (or Pinecone for scaling)
- **Orchestration Layer:** Laravel backend (`GeminiService.php`)

### 2.2 Data Flow
1. **User Query Input:** Captures user prompt, role, intent, language, and conversation history.
2. **Intent Classification & Retrieval:** Queries the Vector DB for semantically similar knowledge chunks.
3. **Context Injection:** Retrieves top-K matches from app documentation, API references, and troubleshooting guides.
4. **Few-Shot Prompting:** Injects edge cases and historical resolutions into the prompt.
5. **Generation & Filtering:** Gemini generates the response, validated against hallucination thresholds.
6. **Delivery & Logging:** Logs the interaction for continuous learning and user satisfaction scoring.

## 3. Training Methodology

### 3.1 Knowledge Base Construction (RAG)
All internal documentation (`docs/*.md`), feature descriptions, and business logic are parsed, chunked (500-token limit with 50-token overlap), and embedded into the searchable vector database.
- **Documents Included:** `api_public.md`, `vtu_backend.md`, `webhook_security.md`, `PROJECT_PROPOSAL.md`, etc.
- **Update Cycle:** Automated daily synchronization triggered by repository changes.

### 3.2 Multi-Model Fine-Tuning
- **Supervised Fine-Tuning (SFT):** Training datasets are constructed from historical support tickets (resolved queries) to align the model's tone and domain accuracy.
- **Reinforcement Learning from Human Feedback (RLHF):** Explicit feedback (thumbs up/down) and implicit metrics (conversation completion) are used to adjust response weights.
- **Few-Shot Learning:** Edge-case scenarios (e.g., specific API failure codes) are appended to system prompts dynamically based on intent classification.

### 3.3 Context-Aware Generation
- **Conversation Memory:** Stores the last 10 turns of interaction in the session database.
- **Personalization:** Tailors answers based on user roles (Admin vs. Agent vs. Standard User).
- **Multilingual Support:** The system prompt instructs Gemini to auto-detect user language and respond natively while maintaining technical accuracy.

## 4. Real-Time Validation & Automated Testing
- **Test Dataset:** A structured set of 500+ Q&A pairs covering functional, technical, and business domains.
- **Metrics Evaluated:** 
  - *Precision:* > 95% (relevance of provided answers)
  - *Recall:* > 95% (ability to retrieve correct documentation)
  - *Hallucination Rate:* < 1%
- **Automated Validation Scripts:** A scheduled Artisan command (`php artisan chatbot:evaluate`) runs daily tests against the dataset, generating performance evaluation reports.

## 5. Continuous Learning Mechanism
- **Feedback Loop:** User feedback on chatbot responses is stored in `chatbot_feedbacks`.
- **Weekly Retraining:** An automated job extracts queries with low satisfaction scores or unresolved status, requiring admin review. Once reviewed and corrected, these are added to the Few-Shot dataset or vector store to improve future accuracy.

## 6. Fallback & Human Handoff Protocol
If the model detects low confidence (< 0.7) or receives sequential negative feedback, it triggers the **Human Handoff Protocol**:
1. Informs the user of the transfer.
2. Creates a support ticket (`Ticket` model) with the full conversation transcript.
3. Flags the query as a "Knowledge Gap" in the monitoring dashboard for documentation updates.

## 7. Deliverables & Integration
- **API Endpoints:** `POST /api/chatbot/chat`, `POST /api/chatbot/feedback`, `POST /api/chatbot/handoff`.
- **Monitoring Dashboards:** Admin interface tracking metrics (Resolution Rate, Average Handle Time, Accuracy).
- **Maintenance Procedures:** Described in this document, handled by Laravel Scheduled Jobs (`app/Console/Kernel.php`).
