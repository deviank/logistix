 # Build an LLM from scratch: a practical roadmap
 
 This guide is a focused, end-to-end learning path for building a small
 transformer language model from first principles. It is intentionally
 scoped to a single developer with limited compute, and it prioritizes
 correctness, clarity, and a clear progression of milestones.
 
 ---
 
 ## What "from scratch" means
 
 In practice, "from scratch" usually means:
 
 - You implement the model architecture yourself (not just call a
   prebuilt model).
 - You build a data pipeline and tokenizer suitable for training.
 - You train the model weights yourself (even if small).
 - You do not start from pretrained checkpoints.
 
 You can still use a framework like PyTorch or JAX for tensor ops and
 autograd. Rewriting CUDA kernels is not required.
 
 ---
 
 ## Prerequisites (learn or refresh)
 
 **Math**
 - Linear algebra: matrix multiplication, dot products, eigen basics
 - Probability: conditional probability, softmax, cross-entropy
 
 **Programming**
 - Python proficiency
 - Familiarity with a deep learning framework (PyTorch recommended)
 
 **Compute**
 - A single GPU is enough for a toy model
 - CPU-only is possible but very slow
 
 ---
 
 ## Step 1: Start with a tiny baseline
 
 Before transformers, build a trivial model so you understand the core
 language modeling loop.
 
 **Milestone**
 - Build a character-level bigram model.
 - Train on a small text file.
 - Sample text and verify it learns basic structure.
 
 You will learn:
 - Dataset batching and train/val split
 - Loss computation and backprop
 - Sampling from a categorical distribution
 
 ---
 
 ## Step 2: Data pipeline
 
 **Key tasks**
 1. Collect a small, clean text corpus you are allowed to use.
 2. Normalize text (line breaks, whitespace).
 3. Split into train/validation/test (example: 90/5/5).
 
 **Tips**
 - Avoid data leakage: do not mix train and evaluation text.
 - Keep a checksum or versioned snapshot of the data.
 
 ---
 
 ## Step 3: Tokenization (BPE or unigram)
 
 A byte-pair encoding (BPE) tokenizer is the most approachable to
 implement.
 
 **Milestone**
 - Build a BPE vocabulary (5k to 20k tokens).
 - Encode and decode text.
 - Verify that encode(decode(x)) preserves x.
 
 **Why it matters**
 - Tokenization defines the model's input and output space.
 - Vocabulary size impacts model size and training speed.
 
 ---
 
 ## Step 4: Transformer core
 
 A minimal transformer decoder stack includes:
 
 - Token embeddings
 - Positional embeddings
 - Repeated blocks of:
   - LayerNorm
   - Multi-head self-attention
   - Feed-forward MLP
   - Residual connections
 - Final LayerNorm
 - Output projection to vocabulary
 
 **Reference equations (conceptual)**
 - Attention: softmax(QK^T / sqrt(d_k)) V
 - FFN: Linear -> GELU -> Linear
 
 **Milestone**
 - Implement a single transformer block
 - Stack N blocks (start with N=2 to 4)
 - Verify forward pass output shape
 
 ---
 
 ## Step 5: Training loop
 
 **Loss**
 - Standard language modeling loss: cross-entropy of next token
 
 **Optimizer**
 - AdamW is the default choice
 - Use gradient clipping (e.g., 1.0) for stability
 
 **Learning rate schedule**
 - Warmup (e.g., 200 to 1,000 steps)
 - Cosine decay to a small floor
 
 **Milestone**
 - Train until validation loss stops improving
 - Sample outputs during training for sanity checks
 
 **Rule of thumb for small models**
 - Batch size: 32 to 128
 - Sequence length: 128 to 512
 - Learning rate: 1e-4 to 3e-4
 
 ---
 
 ## Step 6: Evaluation
 
 **Primary metric**
 - Perplexity (exp of cross-entropy loss)
 
 **Sanity tests**
 - Can it complete common words correctly?
 - Does it learn punctuation and basic syntax?
 
 For larger models, add:
 - Held-out text evaluation
 - Simple task probes (QA, cloze tasks)
 
 ---
 
 ## Step 7: Inference and sampling
 
 Implement a small text generation function:
 
 - Temperature scaling
 - Top-k or nucleus (top-p) sampling
 - Cache attention keys/values for speed
 
 **Milestone**
 - Generate 200 to 500 token samples
 - Confirm that higher temperature increases diversity
 
 ---
 
 ## Step 8: Scaling up (optional)
 
 Scaling can be a second phase once the pipeline works.
 
 **Levers**
 - More data
 - Larger model (d_model, layers)
 - Longer context (sequence length)
 
 **Compute rule of thumb**
 - Training FLOPs ~ 6 * N_params * N_tokens
 - This helps you budget model size and data volume
 
 ---
 
 ## Common pitfalls
 
 - **Data leakage**: mixing test text into training
 - **Tokenization bugs**: broken decode or mismatched vocab
 - **Training instability**: exploding loss due to LR too high
 - **Overfitting**: validation loss rising while train loss falls
 - **Mismatch**: using a tokenizer different from the model's vocab
 
 ---
 
 ## Suggested milestone plan (4 to 6 weeks)
 
 1. **Week 1**: Bigram model, sampling, training loop
 2. **Week 2**: BPE tokenizer + dataset pipeline
 3. **Week 3**: Transformer block and minimal model
 4. **Week 4**: Train, evaluate, and sample outputs
 5. **Week 5-6**: Stability improvements and scaling experiments
 
 ---
 
 ## Minimal project structure
 
 ```
 llm-from-scratch/
 ├── data/
 │   ├── raw.txt
 │   └── train.bin
 ├── tokenizer/
 │   ├── bpe.py
 │   └── vocab.json
 ├── model/
 │   ├── transformer.py
 │   └── layers.py
 ├── train.py
 ├── sample.py
 └── README.md
 ```
 
 ---
 
 ## Optional reading (short list)
 
 - "Attention Is All You Need" (Transformer paper)
 - "The Annotated Transformer" (practical walkthrough)
 - Open-source small-model examples (nanoGPT, minGPT)
 
 ---
 
 ## If you want a next step
 
 If you share your current skill level and available compute (CPU only,
 single GPU, or multiple GPUs), I can tailor a concrete, step-by-step
 training plan with model sizes and hyperparameters that will actually
 run on your hardware.
